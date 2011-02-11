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

add_action('init', 'mbpc_create_my_post_types');

function mbpc_create_my_post_types() {
	register_post_type('sermon',
						array(
							'labels'=>array(
										'name'=>__('Sermons'),
										'singular_name'=>__('Sermon'),
										'add_new'=>__('Add New'),
										'add_new_item'=>__('Add New Sermon'),
										'edit'=>__('Edit'),
										'edit_item'=>__('Edit Sermon'),
										'new_item'=>__('New Sermon'),
										'view_item'=>__('View Sermon'),
										'search_items'=>__('Search Sermons'),
										'not_found'=>__('No sermons found'),
										'not_found_in_trash'=>__('No sermons found in trash')),
							'description'=>__('Contains a link to the recording of the sermon as well as the text of the sermon summary'),
							'public'=>true,
							'menu_position'=>5
						)
						);
}

add_action('init', 'mbpc_register_taxnomies');

function mbpc_register_taxnomies() {
	register_taxonomy(
		'speaker',
		array('sermon'),
		array(
			'labels'=>array(
						'name'=>__('Speakers'),
						'singular_name'=>__('Speaker'),
						'search_items'=>__('Search for Speakers'),
						'popular_items'=>__('Popular Speakers'),
						'all_items'=>__('All Speakers'),
						'edit_item'=>__('Edit Speaker'),
						'update_item'=>__('Update Speaker'),
						'add_new_item'=>__('Add New Speaker'),
						'new_item_name'=>__('Name of New Speaker'),
						'add_or_remove_items'=>__('Add or remove speakers')
						),
			'hierarchical'=>true
			)
		);

}

?>
