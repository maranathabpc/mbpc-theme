<?php

function home_page_menu_args($args) {
	$args['show_home'] = true;
	return $args;
}

add_filter('wp_page_menu_args', 'home_page_menu_args');

?>
