<?php
	namespace FVPUPE;
	defined( 'ABSPATH' ) or die();

	class Playlist {

		/*
		* Class construct
		*/
		public function __construct() {
			$this->initPlaylist();
		}


		/*
		* Init playlist
		*/
		public function initPlaylist() {
			$this->CreateCPTs();

			add_action('wp_ajax_ajaxGetUserPlaylists', array($this, 'ajaxGetUserPlaylists'));
			add_action('wp_ajax_nopriv_ajaxGetUserPlaylists', array($this, 'ajaxGetUserPlaylists'));

			add_action('wp_ajax_ajaxGetPlaylistById', array($this, 'ajaxGetPlaylistById'));
			//add_action('wp_ajax_nopriv_ajaxGetPlaylistById', array($this, 'ajaxGetPlaylistById'));

			add_action('wp_ajax_ajaxSavePlaylistOrder', array($this, 'ajaxSavePlaylistOrder'));
			//add_action('wp_ajax_nopriv_ajaxSavePlaylistOrder', array($this, 'ajaxSavePlaylistOrder'));

			add_action('wp_ajax_ajaxSaveVideosOrder', array($this, 'ajaxSaveVideosOrder'));
			//add_action('wp_ajax_nopriv_ajaxSaveVideosOrder', array($this, 'ajaxSaveVideosOrder'));

			add_action('wp_ajax_ajaxSaveUserPlaylist', array($this, 'ajaxSaveUserPlaylist'));
			//add_action('wp_ajax_nopriv_ajaxSaveUserPlaylist', array($this, 'ajaxSaveUserPlaylist'));

			add_action('wp_ajax_ajaxAddToPlaylist', array($this, 'ajaxAddToPlaylist'));
			//add_action('wp_ajax_nopriv_ajaxAddToPlaylist', array($this, 'ajaxAddToPlaylist'));	

			add_action('wp_ajax_ajaxDeleteUserPlaylist', array($this, 'ajaxDeleteUserPlaylist'));
			//add_action('wp_ajax_nopriv_ajaxDeleteUserPlaylist', array($this, 'ajaxDeleteUserPlaylist'));	

			//add_action('wp_ajax_ajaxSavePlaylistName', array($this, 'ajaxSavePlaylistName'));
			//add_action('wp_ajax_nopriv_ajaxSavePlaylistName', array($this, 'ajaxSavePlaylistName'));	

			add_action('wp_ajax_ajaxDeleteVideoFromPlaylist', array($this, 'ajaxDeleteVideoFromPlaylist'));
			//add_action('wp_ajax_nopriv_ajaxDeleteVideoFromPlaylist', array($this, 'ajaxDeleteVideoFromPlaylist'));				

			add_action('add_meta_boxes', array($this, 'addVideosMetaBox'));
		}

		/*
		* AJAX: Save videos order
		*/
		public function ajaxSaveVideosOrder() {
			$order 			= (isset($_POST['order'])) ? $_POST['order'] : array();
			$playlist_id 	= (isset($_POST['playlist_id'])) ? $_POST['playlist_id'] : false;

			if (!empty($order) && $playlist_id) {

				if ($this->isUsersPlaylist($playlist_id)) {
					$playlist = $this->getPlaylistById($playlist_id);
					$videos_order = array();
					foreach($order as $video) {

						foreach ($playlist->videos as $pl_video) {							
							if ($pl_video['video_id'] == $video['video_id'] && $pl_video['page_id'] == $video['page_id']) {
								$videos_order[] = $video;
							}
						}
					}
					update_post_meta($playlist_id, 'fvpupe_videos', $videos_order);
				}
			}

			echo json_encode(array(
				'status'	=> 1,
				'message'	=> apply_filters('fvpupe_phrase_order_saved', __('The order has been saved.', 'fvpupe'))
			));
			die();
		}

		/*
		* AJAX: Save playlists order
		*/
		public function ajaxSavePlaylistOrder() {
			$order = (isset($_POST['order'])) ? $_POST['order'] : array();

			if (!empty($order)) {
				$order_num = 1;
				foreach($order as $id) {

					if ($this->isUsersPlaylist($id)) {
						$order_num++;
						update_post_meta($id, 'fvpupe_order', $order_num);
					}

				}
			}

			echo json_encode(array(
				'status'	=> 1,
				'message'	=> apply_filters('fvpupe_phrase_order_saved', __('The order has been saved.', 'fvpupe'))
			));
			die();
		}


		/*
		* AJAX: Delete playlist and videos
		*/
		public function ajaxDeleteUserPlaylist() {
			$playlist_id = $_POST['playlist_id'];
			$playlist = $this->getUserPlaylist($playlist_id);
			
			if ($playlist && $this->isUsersPlaylist($playlist_id)) {
				wp_delete_post($playlist_id, true);
			}

			echo json_encode(array(
				'status'	=> 1,
				'message'	=> apply_filters('fvpupe_phrase_playlist_deleted', __('The playlist has been deleted.', 'fvpupe')),
				'archive'	=> get_post_type_archive_link('fvpupe_playlists')
			));
			die();
		}

		/*
		* AJAX: Get specific playlist
		*/
		public function ajaxGetPlaylistById() {

			$id = $_POST['id'];

			if (!empty($id)) {
				$playlist = $this->getPlaylistById($id);
				if (!empty($playlist)) {
					$data = array(
						'name'			=> $playlist->post_title,
						'description'	=> $playlist->description,
						'videos'		=> $playlist->videos,
					);

					echo json_encode(array(
						'status'	=> 1,
						'data'		=> $data
					));
					die();					
				}
			}

			echo json_encode(array(
				'status'	=> 2,
				'message'	=> apply_filters('fvpupe_phrase_no_playlists_found', __('No playlists were found.', 'fvpupe'))
			));
			die();

			return false;
		}


		/*
		* Get specific user playlist
		*/
		public function getUserPlaylist($playlist_id) {
			$playlists = $this->getUserPlaylists();
			if ($playlists['status'] == 1) {
				foreach ($playlists['playlists'] as $playlist) {
					if ($playlist->ID == $playlist_id)
						return $playlist;
				}
			}
			return false;
		}

		/*
		* Add meta box to admin
		*/
		public function addVideosMetaBox() {
			add_meta_box('playlist-videos', __('Playlist videos', 'fvpupe'), array($this, 'fillVideosMetaBox'), 'fvpupe_playlists', 'normal', 'high');
			add_meta_box('playlist-order', __('Playlist order', 'fvpupe'), array($this, 'fillOrderMetaBox'), 'fvpupe_playlists', 'normal', 'high');
			add_meta_box('playlist-description', __('Playlist description', 'fvpupe'), array($this, 'fillDescriptionMetaBox'), 'fvpupe_playlists', 'normal', 'high');
		}

		/*
		* Fill videos meta box in admin
		*/
		public function fillVideosMetaBox() {
			global $post;
			print_r(get_post_meta($post->ID, 'fvpupe_videos', true));
		}

		/*
		* Fill order meta box in admin
		*/
		public function fillOrderMetaBox() {
			global $post;
			print_r(get_post_meta($post->ID, 'fvpupe_order', true));
		}

		/*
		* Fill description meta box in admin
		*/
		public function fillDescriptionMetaBox() {
			global $post;
			print_r(get_post_meta($post->ID, 'fvpupe_description', true));
		}

		/*
		* AJAX: Add user playlists
		*/
		public function modifyPlaylist($action, $video_id, $page_id, $playlist_id) {

			$videos = get_post_meta($playlist_id, 'fvpupe_videos', true);
			if (empty($videos))
				$videos = array();

			$data = array('video_id' => $video_id, 'page_id' => $page_id);

			if ($action == 'add') {
				if (!in_array($data, $videos)) {
					$videos[] = $data;
				}
			} else if ($action == 'remove') {
				if (in_array($data, $videos)) {
					if (($key = array_search($data, $videos)) !== false) {
						unset($videos[$key]);
					}
				}
			}

			update_post_meta($playlist_id, 'fvpupe_videos', $videos);

		}

		/*
		* AJAX: Add user playlists
		*/
		public function ajaxDeleteVideoFromPlaylist() {
			$playlist_id = (isset($_POST['playlist_id'])) ? $_POST['playlist_id'] : false;
			$video_id = (isset($_POST['video_id'])) ? $_POST['video_id'] : false;
			$page_id = (isset($_POST['page_id'])) ? $_POST['page_id'] : false;

			if ($this->isUsersPlaylist($playlist_id)) {

				$playlist = get_post($playlist_id);

				if (empty($playlist)) {				
					echo json_encode(array(
						'status'	=> 2,
						'message'	=> apply_filters('fvpupe_phrase_error_occured', __('An error occured, please try again.', 'fvpupe')),
					));
					die();
				}

				$this->modifyPlaylist('remove', $video_id, $page_id, $playlist_id);

			}

			echo json_encode(array(
				'status'	=> 1,
				'message'	=> apply_filters('fvpupe_phrase_video_removed_from', __('Video removed from', 'fvpupe')) . ' ' . $playlist->post_title . '.'
			));
			die();
		}


		/*
		* AJAX: Add user playlists
		*/
		public function ajaxAddToPlaylist() {

			$fvpupe = new FVPUPEPlugin();
			$userid = $fvpupe->GetUserID();
			$playlists = (isset($_POST['fvpupe-playlists'])) ? $_POST['fvpupe-playlists'] : false;
			$video_id = (isset($_POST['fvpupe-videoid'])) ? $_POST['fvpupe-videoid'] : false;
			$page_id = (isset($_POST['fvpupe-pageid'])) ? $_POST['fvpupe-pageid'] : false;

			if (!$userid) {
				echo json_encode(array(
					'status'	=> 2,
					'message'	=> apply_filters('fvpupe_phrase_need_login_to_add_to_playlist', __('You have to be logged in to add to a playlist. Please login and try again.', 'fvpupe'))
				));
				die();
			}

			if (!$video_id) {
				echo json_encode(array(
					'status'	=> 4,
					'message'	=> apply_filters('fvpupe_phrase_no_video_found', __('No video found.', 'fvpupe'))
				));
				die();
			}

			$message = apply_filters('fvpupe_phrase_changes_saved', __('The changes have been saved.', 'fvpupe'));

			$user_playlists = $this->getUserPlaylists();
			if ($user_playlists['status'] == 1) {

				$data = array('video_id' => $video_id, 'page_id' => $page_id);
				/*print_r($data);
				die();*/

				foreach($user_playlists['playlists'] as $playlist) {

					$videos = get_post_meta($playlist->ID, 'fvpupe_videos', true);
					if (empty($videos))
						$videos = array();

					if (in_array($playlist->ID, $playlists)) {
						if (!in_array($data, $videos)) {
							$this->modifyPlaylist('add', $video_id, $page_id, $playlist->ID);
							$message = apply_filters('fvpupe_phrase_video_added_to', __('Video added to', 'fvpupe')) . ' ' . $playlist->post_title . '.';
						}
					} else {						
						if (in_array($data, $videos)) {							
							if (($key = array_search($data, $videos)) !== false) {
								$this->modifyPlaylist('remove', $video_id, $page_id, $playlist->ID);
								$message = apply_filters('fvpupe_phrase_video_removed_from', __('Video removed from', 'fvpupe')) . ' ' . $playlist->post_title . '.';
							}
						}
					}
				}
			}

			echo json_encode(array(
				'status'	=> 1,
				'message'	=> $message
			));
			die();
		}


		/*
		* Check if current user created playlist
		*/
		public function isUsersPlaylist($playlist_id) {
			$fvpupe = new FVPUPEPlugin();
			$userid = $fvpupe->GetUserID();
			$playlist = get_post($playlist_id);
			if (!empty($playlist)) {
				if ($playlist->post_author == $userid)
					return true;
			}
			return false;
		}

		/*
		* Get next order number
		*/
		public function getNextOrderNumber() {
			$playlists = $this->getUserPlaylists();
			if ($playlists['status'] == 1) {
				$current = end($playlists['playlists']);
				$order = $current->order;
				return (int)$order + 1;
			}
			return 1;
		}


		/*
		* AJAX: Add user playlists
		*/
		public function ajaxSaveUserPlaylist() {
			
			$name = $_POST['fvpupe-playlist-name'];
			$description = $_POST['fvpupe-playlist-description'];
			$fvpupe = new FVPUPEPlugin();
			$userid = $fvpupe->GetUserID();
			$video_id = (isset($_POST['fvpupe-videoid'])) ? $_POST['fvpupe-videoid'] : false;
			$page_id = (isset($_POST['fvpupe-pageid'])) ? $_POST['fvpupe-pageid'] : false;
			$playlist_id = (isset($_POST['fvpupe-playlistid'])) ? $_POST['fvpupe-playlistid'] : false;
			$is_new = (!$playlist_id);

			if (!$userid) {
				echo json_encode(array(
					'status'	=> 4,
					'message'	=> apply_filters('fvpupe_phrase_need_login_to_create_playlist', __('You have to be logged in to create a playlist. Please login and try again.', 'fvpupe'))
				));
				die();
			}


			if (!empty($name)) {

				$fvpupe = new FVPUPEPlugin();
				$name = strip_tags($name);
				$name = str_replace('>', '', $name);
				$name = str_replace('<', '', $name);

				$playlists = $this->getUserPlaylists();
				if ($playlists['status'] == 1) {
					foreach($playlists['playlists'] as $pl) {
						if ($pl->post_title == $name && $playlist_id != $pl->ID) {
							echo json_encode(array(
								'status'	=> 5,
								'message'	=> apply_filters('fvpupe_phrase_playlist_name_already_exists', __('You already have a playlist with this name.', 'fvpupe')),
							));
							die();
						}
					}
				}

				$post_data = array();
				$save_phrase = apply_filters('fvpupe_phrase_video_added_to', __('Video added to', 'fvpupe')) . ' ' . $name . '.';

				if ($playlist_id) {
					if (!$this->isUsersPlaylist($playlist_id)) {
						echo json_encode(array(
							'status'	=> 6,
							'message'	=> apply_filters('fvpupe_phrase_not_your_playlist', __('This does not appear to be your playlist', 'fvpupe')),
						));
						die();
					}

					$post_data['ID'] = $playlist_id;
					$save_phrase = apply_filters('fvpupe_phrase_playlist_saved', __('The playlist has been saved.', 'fvpupe'));
				}

				$post_data['post_title']	= $name;
				$post_data['post_status']	= 'publish';
				$post_data['post_author']	= $userid;
				$post_data['post_type']		= 'fvpupe_playlists';

				if (!$playlist_id) {
					$post_data['post_name']	= rand(1000000, 9999999);
					$playlist_id = wp_insert_post( $post_data );									
				} else {
					$playlist_id = wp_update_post( $post_data );
				}				
				if(!is_wp_error($playlist_id)){

					if ($video_id)
						$this->modifyPlaylist('add', $video_id, $page_id, $playlist_id);

					update_post_meta($playlist_id, 'fvpupe_description', $description);

					if ($is_new) {
						$order = $this->getNextOrderNumber();
						update_post_meta($playlist_id, 'fvpupe_order', $order);
					}


					echo json_encode(array(
						'status'	=> 1,
						'message'	=> $save_phrase,
						'id'		=> $playlist_id
					));
				} else {
					echo json_encode(array(
						'status'	=> 3,
						'message'	=> apply_filters('fvpupe_phrase_error_occured', __('An error occured, please try again.', 'fvpupe')),
						'error'		=> $playlist_id->get_error_message()
					));
				}

			} else {
				echo json_encode(array(
					'status'	=> 2,
					'message'	=>  apply_filters('fvpupe_phrase_playlist_name_mandatory', __('Playlist name is mandatory.', 'fvpupe')),
				));
			}

			die();
		}


		/*
		* AJAX: Get user playlists
		*/
		public function ajaxGetUserPlaylists() {
			echo json_encode($this->getUserPlaylists());
			die();
		}

		/*
		* Get videos in playlist
		*/
		public function getVideosInPlaylist($playlist_id) {
			$videos = get_post_meta($playlist_id, 'fvpupe_videos', true);
			if (!empty($videos)) {
				foreach($videos as $key => $video) {
					if (get_post_status($video['page_id']) == 'publish') {
						$page_title = get_the_title($video['page_id']);
						$videos[$key]['page_title'] = $page_title;
					} else {
						unset($videos[$key]);
					}
				}
			}
			return $videos;
		}


		/*
		* Get user playlists
		*/
		public function getUserPlaylists() {

			$fvpupe = new FVPUPEPlugin();
			$userid = $fvpupe->GetUserID();

			if ($userid) {
				$playlists = get_posts(array(
					'post_type'			=> 'fvpupe_playlists',
					'posts_per_page'	=> -1,
					'author'			=> $userid,
					'meta_key'			=> 'fvpupe_order',
					'orderby'			=> 'meta_value',
					'order'				=> 'ASC'
				));

				if (empty($playlists)) {
					return array(
						'status'	=> '2',
						'message'	=> apply_filters('fvpupe_phrase_no_playlists_found', __('No playlists were found.', 'fvpupe'))
					);
				} else {

					foreach($playlists as $key => $playlist) {
						$playlists[$key] = $this->getPlaylistById($playlist->ID);
					}

					return array(
						'status'		=> '1',
						'playlists'		=> $playlists,
					);
				}

			} else {
				return array(
					'status'	=> '3',
					'message'	=> apply_filters('fvpupe_phrase_need_login_to_add_to_playlist', __('You need to login to add to playlists.', 'fvpupe'))
				);
			}

		}


		/*
		* Get playlist by ID
		*/
		public function getPlaylistById($id) {
			if ($id) {
				$playlist = get_post($id);
				if (!empty($playlist)) {
					$videos = $this->getVideosInPlaylist($playlist->ID);
					if (empty($videos))
						$videos = array();

					$playlist->description = get_post_meta($playlist->ID, 'fvpupe_description', true);
					$playlist->order = get_post_meta($playlist->ID, 'fvpupe_order', true);
					$playlist->videos = $videos;
					return $playlist;
				}
			}
			return false;
		}


		/*
		* Create custom post type
		*/
		private function createCPTs() {			

		    $labels = array(
		        'name'                  => __( 'My playlists', 'fvpupe' ),
		        'singular_name'         => _x( 'User playlist', 'Post type singular name', 'fvpupe' ),
		        'menu_name'             => _x( 'User playlists', 'Admin Menu text', 'fvpupe' ),
		        'name_admin_bar'        => _x( 'User playlist', 'Add New on Toolbar', 'fvpupe' ),
		        'add_new'               => __( 'Add new', 'fvpupe' ),
		        'add_new_item'          => __( 'Add new', 'fvpupe' ),
		        'new_item'              => __( 'New', 'fvpupe' ),
		        'edit_item'             => __( 'Edit', 'fvpupe' ),
		        'view_item'             => __( 'View', 'fvpupe' ),
		        'all_items'             => __( 'All', 'fvpupe' ),
		        'search_items'          => __( 'Search', 'fvpupe' ),
		        'parent_item_colon'     => __( 'Parent:', 'fvpupe' ),
		        'not_found'             => __( 'No playlists were found.', 'fvpupe' ),
		        'not_found_in_trash'    => __( 'No playlists were found in trash.', 'fvpupe' ),
		        'featured_image'        => _x( 'Playlist cover image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'fvpupe' ),
		        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'fvpupe' ),
		        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'fvpupe' ),
		        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'fvpupe' ),
		        'archives'              => _x( 'Playlists archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'fvpupe' ),
		        'insert_into_item'      => _x( 'Insert into playlist', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'fvpupe' ),
		        'uploaded_to_this_item' => _x( 'Uploaded to this playlist', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'fvpupe' ),
		        'filter_items_list'     => _x( 'Filter playlists list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'fvpupe' ),
		        'items_list_navigation' => _x( 'Playlists list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'fvpupe' ),
		        'items_list'            => _x( 'Playlists list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'fvpupe' ),
		    );     
		    $args = array(
		        'labels'             => $labels,
		        'description'        => '',
		        'public'             => true,
		        'publicly_queryable' => true,
		        'show_ui'            => true,
		        'show_in_menu'       => false,
		        'query_var'          => true,
		        'rewrite'            => array( 'slug' => _x('myplaylists', 'User playlist slug', 'fvpupe') ),
		        'capability_type'    => 'post',
		        'has_archive'        => true,
		        'hierarchical'       => false,
		        'menu_position'      => 5,
		        'supports'           => array( 'title', 'author', 'thumbnail' ),
		        'show_in_rest'       => false
		    );
		     
		    register_post_type( 'fvpupe_playlists', $args );

		}

	}
?>