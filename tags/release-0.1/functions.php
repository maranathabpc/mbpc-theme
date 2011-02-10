<?php

function home_page_menu_args($args) {
	$args['show_home'] = true;
	return $args;
}

add_filter('wp_page_menu_args', 'home_page_menu_args');

//hide search form, especially from 404 page
add_filter('get_search_form', create_function('$a',"return null;"));

function ubuntu_font() {
?>
    <link href='http://fonts.googleapis.com/css?family=Ubuntu:regular,italic,bold' rel='stylesheet' type='text/css'>
<?php
}
add_action('wp_head', 'ubuntu_font');
?>

