<?php
	namespace FVPUPE;
	defined( 'ABSPATH' ) or die();

	class Shortcode {		

		/*
		* Class construct
		*/
		public function __construct() {
			$this->initShortcodes();
		}

		/*
		* Init shortcode
		*/
		public function initShortcodes() {

			remove_shortcode( 'fvplayer' );
			add_shortcode( 'fvplayer', array('\FVPUPE\Shortcode', 'FVPlayerExtension') );
			add_shortcode( 'fvplayer_userplaylists', array('\FVPUPE\Shortcode', 'MainShortcode') );
		}

		public static function ImplodeToParameters($atts) {
			$value = '';
			if (!empty($atts)) {
				foreach ($atts as $k => $v) {
					$value .= ' ' . $k  . '="' . $v . '"';
				}
			}
			return $value;
		}


		/*
		* Extend [fvplayer] shortcode
		*/
		public static function FVPlayerExtension($atts = array()) {


			if (isset($atts['module'])) {
				if (in_array(strtolower($atts['module']), array('userplaylist', 'userplaylists', 'userplaylist_button'))) {
					$sc = new Shortcode();
					return do_shortcode('[fvplayer_userplaylists' . $sc->ImplodeToParameters($atts) . ']');
				}
			}


			if (!function_exists('flowplayer_content_handle'))
				return '<em>' . __('FV Player plugin not found.', 'fvpupe') . '</em>';

			$html = flowplayer_content_handle( $atts );

			$show = apply_filters('fvpupe_settings_auto_add_userplaylist_button', true);
			if (isset($atts['userplaylist_button'])) {
				if (strtolower($atts['userplaylist_button']) == 'true' || $atts['userplaylist_button'] == '1')
					$show = true;

				if (strtolower($atts['userplaylist_button']) == 'false' || $atts['userplaylist_button'] == '2')
					$show = false;
			}

			if ($show) {

				$video_id = false;
				if (isset($atts['src']))
					$video_id = $atts['src'];

				if (isset($atts['id']))
					$video_id = $atts['id'];

				if ($video_id) {

					$html .= do_shortcode('[fvplayer_userplaylists module="userplaylist_button" video_id="' . $video_id . '"]');
				}
			}

			return $html;

		}

		/*
		* Main shortcode
		*/
		public static function MainShortcode($atts = array()) {
	        $a = shortcode_atts( array(
	        	'module'	=> false,
	        	'video_id'	=> false,
	        	'page_id'	=> false,
	        	'id'		=> get_the_ID(),
	        	'content'	=> 'full',
	        	'liststyle'	=> apply_filters('fvpupe_setting_single_template_playlist_style', 'horizontal'),
	        ), $atts );

	        $sc = new Shortcode();

	        $html = '';

	        if ($a['module'] == 'userplaylist_button') {

	        	$page_id = (isset($a['page_id']) && !empty($a['page_id'])) ? $a['page_id'] : get_the_ID();
	        	$video_id = (isset($a['video_id']) && !empty($a['video_id'])) ? $a['video_id'] : false;

	        	$html .= '<a class="' . apply_filters('fvpupe_css_class_add_to_playlist_button', 'fvpupe-button') . '" href="#" data-fvpupe-add-to-playlist data-fvpupe-video-id="' . $video_id . '" data-fvpupe-page-id="' . $page_id . '">';
	        	$html .= apply_filters('fvpupe_html_before_add_to_playlist_button_text', '');
	        	$html .= apply_filters('fvpupe_phrase_add_to_playlist', __('Add to playlist', 'fvpupe'));
	        	$html .= apply_filters('fvpupe_html_after_add_to_playlist_button_text', '');
	        	$html .= '</a>';
	        }

	        if ($a['module'] == 'userplaylists') {	        	
	        	$html .= $sc->ArchivePageHTML();
	        }

	        if ($a['module'] == 'userplaylist') {
	        	$html .= $sc->SinglePageHTML($a);
	        }

	        return $html;
		}		

		/*
		* Single Page HTML
		*/
		public static function SinglePageHTML($a) {

			$id = $a['id'];

			$pl = new Playlist();
			$fvpupe = new FVPUPEPlugin();
			$playlist = $pl->getPlaylistById($id);

			if (!$playlist) {
				return '<em>' . apply_filters('fvpupe_phrase_playlist_not_found', __('Playlist not found', 'fvpupe')) . '</em>';
			}

			$content_playlist = '
					<div class="fvpupe-single-playlist-videos">
						<div class="fvpupe-playlist-video-wrapper">' . $fvpupe->getPlaylistShortcode($playlist->videos, $a) . '</div>
					</div>	
			';

			$content_title 			= '<h1>' . get_the_title($id) . '</h1>';
			$content_author 		= '<span>' . apply_filters('fvpupe_phrase_playlist_created_by', __('Playlist created by', 'fvpupe')) . ': </span>' . get_the_author_meta('display_name', $playlist->post_author);
			$content_description 	= $fvpupe->nl2p($playlist->description) . (($fvpupe->GetUserID() == $playlist->post_author) ? '<p><a href="#" class="fvpupe-button fvpupe-positive" data-fvpupe-fromsingle data-fvpupe-editplaylistname="' . $playlist->ID . '">' . apply_filters('fvpupe_phrase_edit', __('Edit', 'fvpupe')) . '</a> <a href="#" class="fvpupe-button fvpupe-negative" data-fvpupe-fromsingle data-fvpupe-deleteplaylistconfirm="' . $playlist->ID . '">' . apply_filters('fvpupe_phrase_delete', __('Delete', 'fvpupe')) . '</a></p>' : '');

			if ($a['content'] == 'title')
				return $content_title;

			if ($a['content'] == 'playlist')
				return $content_playlist;

			if ($a['content'] == 'author')
				return $content_author;

			if ($a['content'] == 'description')
				return $content_description;

			return '
				<div class="fvpupe-single-playlist-wrapper" data-fvpupe-userplaylist="' . $playlist->ID . '">
					' . $content_playlist . '
					<div class="fvpupe-single-playlist-info">
						' . $content_title . '
						<div class="fvpupe-single-playlist-author">' . $content_author . '</div>
						<div class="fvpupe-single-playlist-description">' . $content_description . '</div>
					</div>
				</div>
			';
		}

		/*
		* Archive Page HTML
		*/
		public static function ArchivePageHTML() {
			$html = '';

        	$pl = new Playlist();
        	$fvpupe = new FVPUPEPlugin();
        	$playlists = $pl->getUserPlaylists();

        	if ($playlists['status'] != '1') {
        		$html .= '<em>' . $playlists['message'] . '</em>';
        	} else {

        		//echo '<br /><br /><br /><br /><br /><br /><br /><br />';

        		$html .= '<ul class="fvpupe-list fvpupe-userplaylists fvpupe-cf" data-fvpupe-userplaylist-list data-fvpupe-sortable-playlist>';
        		foreach($playlists['playlists'] as $playlist) {

					$html .= '<li class="fvpupe-userplaylist" data-fvpupe-userplaylist="' . $playlist->ID . '">';

        			$html .= '<div class="fvpupe-order-handle"><svg class="fvpupe-order-handle-svg" xmlns="http://www.w3.org/2000/svg" width="109.031" height="108.937" viewBox="0 0 109.031 108.937"><path d="M258.658,200.4a5.126,5.126,0,0,1-1.69,3.8l-17.037,15.332a5.111,5.111,0,0,1-8.531-3.8V205.51H209.252v22.148h10.031a5.112,5.112,0,0,1,3.8,8.531l-15.333,17.037a4.563,4.563,0,0,1-3.8,1.69,4.826,4.826,0,0,1-3.8-1.69L185.01,236.189a5.112,5.112,0,0,1,3.8-8.531h10.222V205.51H176.883v10.222a5.11,5.11,0,0,1-3.03,4.668,5.2,5.2,0,0,1-2.081.443,5.114,5.114,0,0,1-3.42-1.311L151.316,204.2a5.116,5.116,0,0,1,0-7.6l17.037-15.332a5.112,5.112,0,0,1,8.531,3.8v10.222h22.148V173.141H188.617a4.993,4.993,0,0,1-3.6-8.531l15.141-17.036a5.287,5.287,0,0,1,7.6,0l15.332,17.036a5.112,5.112,0,0,1-3.8,8.531H209.252v22.148H231.4V185.067a5.112,5.112,0,0,1,8.531-3.8L256.967,196.6A5.123,5.123,0,0,1,258.658,200.4Z" transform="translate(-149.625 -145.969)"/></svg></div>';

        			$html .= '<div class="fvpupe-userplaylist-content-wrapper">';

        			/*$html .= '<div class="fvpupe-playlist-video-wrapper">' . $fvpupe->getPlaylistShortcode($playlist->videos, array(
        				'liststyle' => 'prevnext'
        			)) . '</div>';*/


        			$videos_length = 0;
        			$num_of_videos = 0;
        			$cover_image = '';
        			if (isset($playlist->videos) && !empty($playlist->videos)) {
        				$num_of_videos = count($playlist->videos);
	        			foreach($playlist->videos as $video) {
							if ( empty($cover_image) && !empty($splash = get_the_post_thumbnail_url($video['page_id']))) {
								$cover_image = $splash;
							}
							
							/*$fv_video = new \FV_Player_Db_Video( $video['video_id'] );
							$fv_video = false;

							print_r($video);

							echo 'video_id: ' . $video['video_id'] . '<br />';
							if ($fv_video) {
								echo $fv_video->getMetaValue('duration');
							}

							echo '<br />-------------------<br />';*/
	        			}
        			}

        			$html .= '<div class="fvpupe-playlist-cover-wrapper"><div class="fvpupe-playlist-cover-image" style="background-image: url(' . $cover_image . ');"><img src="' . $fvpupe->pluginPath('url') . '/static/img/blank-square.png" alt="" /></div></div>';


        			$html .= '<div class="fvpupe-userplaylist-info">';

        			$html .= '<a href="' . get_the_permalink($playlist) . '">';
        			$html .= '<h3><span data-fvpupe-playlistname>' . $playlist->post_title . '</span></h3>';

        			$num_of_videos_phrase = ($num_of_videos == 1) ? apply_filters('fvpupe_phrase_video_in_playlist', _x('Video', 'Number of videos in playlist', 'fvpupe')) : apply_filters('fvpupe_phrase_videos_in_playlist', _x('Videos', 'Number of videos in playlist', 'fvpupe'));

        			//$html .= 'Tid: 05:22<br />';
        			$html .= $num_of_videos . ' ' . mb_strtolower($num_of_videos_phrase) . '<br />';
        			$html .= '</a>';
        		
	        		$html .= '</div>';

	        		$html .= '<div class="fvpupe-userplaylist-buttons">';
	        		$html .= '<div class="fvpupe-userplaylist-buttons-inner">';
					//$html .= '<a href="' . get_the_permalink($playlist) . '" class="fvpupe-button-small fvpupe-playplaylist">' . apply_filters('fvpupe_phrase_play', __('Play', 'fvpupe')) . '</a>';
        			
					$html .= '<a href="#" class="fvpupe-button fvpupe-editplaylistname" data-fvpupe-editplaylistname="' . $playlist->ID . '">' . apply_filters('fvpupe_phrase_edit', __('Edit', 'fvpupe')) . '</a>';
        			$html .= '<a href="#" class="fvpupe-button fvpupe-negative fvpupe-deleteplaylistconfirm" data-fvpupe-deleteplaylistconfirm="' . $playlist->ID . '">' . apply_filters('fvpupe_phrase_delete', __('Delete', 'fvpupe')) . '</a>';
					$html .= '</div>';
					$html .= '</div>';

	        		$html .= '</div>';

        			$html .= '</li>';
        		}

        		$html .= '</ul>';
        	}

			return $html;
		}
	}