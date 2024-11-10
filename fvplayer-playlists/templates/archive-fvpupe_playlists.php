<?php
	get_header();
	do_action('fvpupe_template_archive_before_wrapper');
	echo '<div class="fvpupe-outer-wrapper">';
	echo '<div class="fvpupe-wrapper">';
	do_action('fvpupe_template_archive_before_content');
	echo '<h1>' . __('My playlists', 'fvpupe') . '</h1>';
	echo do_shortcode('[fvplayer module="userplaylists"]');
	do_action('fvpupe_template_archive_after_content');
	echo '</div>';
	echo '</div>';
	do_action('fvpupe_template_archive_after_wrapper');
	get_footer();