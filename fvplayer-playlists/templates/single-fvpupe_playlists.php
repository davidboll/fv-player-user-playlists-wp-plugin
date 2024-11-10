<?php
	get_header();
	do_action('fvpupe_template_single_before_wrapper');
	echo '<div class="fvpupe-outer-wrapper">';
	echo '<div class="fvpupe-wrapper">';	
	do_action('fvpupe_template_single_before_content');
	echo do_shortcode('[fvplayer module="userplaylist"]');
	do_action('fvpupe_template_single_after_content');
	echo '</div>';
	echo '</div>';
	do_action('fvpupe_template_single_after_wrapper');
	get_footer();