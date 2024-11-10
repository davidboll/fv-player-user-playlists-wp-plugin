<?php
	defined( 'ABSPATH' ) or die();

/*
	Plugin Name: FV Player, User Playlist Extension
	Description: Let your users create and manage their own playlists.
	Author: Eyecon Media
	Author URI: https://eyeconmedia.se
	Version: 1.0.0
	Text Domain: fvpupe
	Domain Path: /languages
*/

	// require files using autloader
	require_once(dirname(__FILE__) . '/autoload.php');

	// init
	$fvpupe = new FVPUPE\FVPUPEPlugin();
	$fvpupe->initPlugin();
?>