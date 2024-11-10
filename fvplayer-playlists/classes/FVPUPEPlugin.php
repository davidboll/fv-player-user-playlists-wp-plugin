<?php
	namespace FVPUPE;
	defined( 'ABSPATH' ) or die();

	class FVPUPEPlugin {		

		/*
		* Get User ID
		*/
		public function GetUserID() {			
			return get_current_user_id();
		}


		/*
		* Language strings for JavaScript files
		*/
		public function jsLanguageStrings() {
			$strings = array(
				'save' 							=> apply_filters('fvpupe_phrase_save', __( 'Save', 'fvpupe' )),
				'loading'						=> apply_filters('fvpupe_phrase_loading', __( 'Loading', 'fvpupe' )),
				'add_to_playlist'				=> apply_filters('fvpupe_phrase_add_to_playlist', __( 'Add to playlist', 'fvpupe' )),
				'create_new_playlist'			=> apply_filters('fvpupe_phrase_create_new_playlist', __( 'Create new playlist', 'fvpupe' )),
				'cancel'						=> apply_filters('fvpupe_phrase_cancel', __( 'Cancel', 'fvpupe' )),
				'playlist_name'					=> apply_filters('fvpupe_phrase_playlist_name', __( 'Playlist name', 'fvpupe' )),
				'add_playlist'					=> apply_filters('fvpupe_phrase_add_playlist', __( 'Add playlist', 'fvpupe' )),
				'save_playlist'					=> apply_filters('fvpupe_phrase_save_playlist', __( 'Save playlist', 'fvpupe' )),
				'yes'							=> apply_filters('fvpupe_phrase_yes', __('Yes', 'fvpupe')),
				'delete_playlist'				=> apply_filters('fvpupe_phrase_delete_playlist', __('Delete playlist', 'fvpupe')),
				'delete_playlist_confirm_text'	=> apply_filters('fvpupe_phrase_confirm_delete_playlist', __('Are you sure you want to delete this playlist?', 'fvpupe')),
				'edit_playlist'					=> apply_filters('fvpupe_phrase_edit_playlist', __('Edit playlist', 'fvpupe')),
				'description'					=> apply_filters('fvpupe_phrase_description', __('Description', 'fvpupe')),
				'videos'						=> apply_filters('fvpupe_phrase_videos', __('Videos', 'fvpupe')),
				'play'							=> apply_filters('fvpupe_phrase_play', __('Play', 'fvpupe')),
				'exercises'						=> apply_filters('fvpupe_phrase_exercises', __('Exercises', 'fvpupe')),
			);
			return $strings;
		}

		/*
		* Turn new line to paragraph
		*/
		public function nl2p($string) {

			$string = '<p>' . $string . '</p>';
			$string = str_replace( "\r\n\r\n", "</p><p>", $string );
			$string = str_replace( "\r\n", "<br />", $string );
			$string = str_replace("\r", '', $string);
			$string = str_replace("\n", '', $string);
			$string = str_replace("<p></p>", '', $string);

			return $string;
		}


		/*
		* Get relative or absolute path to plugin folder
		*/
		public function pluginPath($type = 'dir') {
			$return = '';
			$folder = 'fvplayer-playlists';

			if ($type == 'dir') {
				$path = plugin_dir_path(__FILE__);
				$path = str_replace('\\', '/', $path);
				$arr = array();
				$arr = explode($folder, $path);
				$return = $arr[0] . $folder;
			} else {
				$return = plugins_url() . '/' . $folder;
			}

			return $return;
		}


		/*
		* Add page to FV Player(?)
		*/
		public function addAdminSubmenuItem() {
			add_submenu_page(
				'fv_player',
				'User Playlists',
				'User Playlists',
				'activate_plugins',
				'fvpupe',
				'' //callback
			);
		}

		/*
		* Generate playlist shortcode
		*/
		public static function getPlaylistShortcode($videos, $atts = array()) {

	        $a = shortcode_atts( array(
	        	'liststyle'	=> 'horizontal',
	        ), $atts );

	        $fvpupe = new FVPUPEPlugin();

			$playlist_str = '';
			$first_src = '';
			if (!empty($videos)) {
				foreach ($videos as $video) {					
					if (empty($first_src)) {

						if (is_numeric($video['video_id']))
							$first_src = 'id="' . $video['video_id'] . '"';

						if (!is_numeric($video['video_id']))
							$first_src = 'src="' . $video['video_id'] . '"';

						if (!empty($splash = get_the_post_thumbnail_url($video['page_id'])))
							$first_src .= ' splash="' . $splash . '"';

					} else {
						$playlist_str .= $video['video_id'];
						if (!empty($splash = get_the_post_thumbnail_url($video['page_id'])))
							$playlist_str .= ',' . $splash;
						$playlist_str .= ';';
					}
				}
				if (!empty($playlist_str))
					$playlist_str = rtrim($playlist_str, ';');

				$shortcode_str = '[fvplayer liststyle="' . $a['liststyle'] . '" userplaylist_button="false" ' . $first_src . ((!empty($playlist_str)) ? ' playlist="' . $playlist_str . '"' : '') . ']';

				return do_shortcode($shortcode_str);

			}

			return '<div class="fvpupe-empty-video"><div class="fvpupe-empty-video-text">' . apply_filters('fvpupe_phrase_playlist_is_empty', __('This playlist appears to be empty.', 'fvpupe')) . '</div><img src="' . $fvpupe->pluginPath('url') . '/static/img/blank-16_9.png" alt="" /></div>';
		}

		/*
		* Init plugin
		*/
		public function initPlugin() {

			add_action( 'init', function() {
				$shortcode = new Shortcode();
				$playlist = new Playlist();
				$template = new Template();
			});

			add_action( 'wp_enqueue_scripts', function($hook) {

				$scripts_version = '1.0.5';

				wp_enqueue_style('fvpupe-main', $this->pluginPath('url') . '/static/css/style.min.css', array(), $scripts_version, 'all' );
				wp_enqueue_script('jquery-ui-touch-punch', $this->pluginPath('url') . '/static/js/jquery.ui.touch-punch.min.js', array('jquery', 'jquery-ui-sortable'), '0.2.3', true);
				wp_enqueue_script('fvpupe-main', $this->pluginPath('url') . '/static/js/scripts.min.js', array('jquery', 'jquery-ui-sortable', 'jquery-ui-touch-punch'), $scripts_version, true);
				wp_localize_script( 'fvpupe-main', 'ajax', array(
					'url' 	=> admin_url( 'admin-ajax.php'),
					'lang' 	=> $this->jsLanguageStrings()
				));
			});

			add_action( 'plugins_loaded', function() {
				load_plugin_textdomain('fvpupe', FALSE, 'fvplayer-playlists/languages/');
			});
		}
	}
?>