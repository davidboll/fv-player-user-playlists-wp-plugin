<?php
	namespace FVPUPE;
	defined( 'ABSPATH' ) or die();

	class Template {		

		/*
		* Class construct
		*/
		public function __construct() {
			add_filter(
				'template_include',
				array( $this, 'checkTemplate'),
				10,
				1
			);
		}

		/*
		* Check loaded template and adjust if needed
		*/
		public function checkTemplate($template) {

			$plugin_post_type = 'fvpupe_playlists';
			$post_type = get_post_type();	

			if (is_post_type_archive($plugin_post_type) || $plugin_post_type == $post_type) {

				$fvpupe = new FVPUPEPlugin();

				// Check if theme is missing archive template, use plugin default 
				if (is_post_type_archive($plugin_post_type)) {

					$pluginArchive = 'archive-' . $plugin_post_type . '.php';
					if (strpos($template, '/themes/') !== false && strpos($template, $pluginArchive) == false) {
						$template = $fvpupe->pluginPath() . '/templates/' . $pluginArchive;

						add_filter( 'body_class', function( $classes ) {
							return array_merge( $classes, array( 'fvpupe-archive-template') );
						});
					}

				} else if ($plugin_post_type == $post_type) {

					// Check if theme is missing single template, use plugin default
					$pluginSingle = 'single-' . $plugin_post_type . '.php';
					if (strpos($template, '/themes/') !== false && strpos($template, $pluginSingle) == false) {

						$template = $fvpupe->pluginPath() . '/templates/' . $pluginSingle;
						add_filter( 'body_class', function( $classes ) {
							return array_merge( $classes, array('fvpupe-single-template') );
						});
					}
				}
			}

			return $template;

		}

	}