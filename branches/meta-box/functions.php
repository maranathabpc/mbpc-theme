<?php

//show home page in Wordpress Menu administration
function home_page_menu_args($args) {
	$args['show_home'] = true;
	return $args;
}

add_filter('wp_page_menu_args', 'home_page_menu_args');

//hide search form, especially from 404 page
add_filter('get_search_form', create_function('$a',"return null;"));

//insert link to ubuntu font
add_action('wp_head', 'ubuntu_font');
function ubuntu_font() {
?>
    <link href='http://fonts.googleapis.com/css?family=Ubuntu:regular,italic,bold' rel='stylesheet' type='text/css'>
<?php
}

//register custom post types
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
							'menu_position'=>5,
							'rewrite'=>array('with_front'=>false)
						)
						);
	//add_post_type_support('sermon','custom-fields');
}

//register custom taxonomies
add_action('init', 'mbpc_register_taxonomies');
function mbpc_register_taxonomies() {
	//speakers for sermons
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

	//themes for sermons
	register_taxonomy(
		'theme',
		array('sermon'),
		array(
			'labels'=>array(
						'name'=>__('Themes'),
						'singular_name'=>__('Theme'),
						'search_items'=>__('Search for Themes'),
						'popular_items'=>__('Popular Themes'),
						'all_items'=>__('All Themes'),
						'edit_item'=>__('Edit Theme'),
						'update_item'=>__('Update Theme'),
						'add_new_item'=>__('Add New Theme'),
						'new_item_name'=>__('Name of New Theme'),
						'add_or_remove_items'=>__('Add or remove themes')
						),
			'hierarchical'=>true
			)
		);

}

//override the posted on function in the parent theme because we don't want the author name
function twentyten_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s', 'twentyten' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		)
	);
}

//add_filter('getarchives_where','mbpc_getarchives_where_filter',10,2);

function mbpc_getarchives_where_filter($where, $r) {
	$args = array(
		'public' => true,
		'_builtin' => false
		);
	$output = 'names';
	$operator = 'and';

	$post_types = get_post_types($args,$output,$operator);
	$post_types = array_merge($post_types, array('post'));
	$post_types = "'" . implode("' , '",$post_types ) . "'";

	return str_replace("post_type = 'post'", "post_type IN ($post_types)", $where);
}

/**
 * This function is a wrapper for 'wp_get_archives' function to support post type filtering.
 * It's necessary to have this function so that the links could be fixed to link to proper archives.
 * This needs to be done here, because WordPress lacks hooks in 'wp_get_archives' function.
 * The links won't be changed if post type is 'all' or type in options is 'postbypost' or 'alpha'.
 *
 * In addition, this function has been modified to work with multi-site. Since multi-site adds blog/
 * to the URL for normal posts, this has to be taken out with the regex.
 * 
 * @param string $post_type post type to get archives from. Or you can use 'all' to include all archives.
 * @param array $args optional args. You can use the same options as in 'wp_get_archives' function.
 * @return string the HTML with correct links if 'echo' option is false. Otherwise will echo that.
 * @see wp_get_archives
 * @link http://codex.wordpress.org/Function_Reference/wp_get_archives
 */
function mbpc_get_post_type_archives($post_type, $args = array()) {
	$echo = isset($args['echo']) ? $args['echo'] : true;
	$type = isset($args['type']) ? $args['type'] : 'monthly';
	
	$args['post_type'] = $post_type;
	$args['echo'] = false;
	
	$html = wp_get_archives($args); // let WP do the hard stuff
	
	if($post_type != 'all' and $type != 'postbypost' and $type != 'alpha') {
		$pattern = 'href=\'' . get_bloginfo('url') . '/blog/';
		$replacement = 'href=\'' . get_the_post_type_permalink($post_type);
		
		$html = str_replace($pattern, $replacement, $html);
	}
	
	if($echo)
		echo $html;
	else
		return $html;
}


/**
 * Archives widget class
 * Modified to use sermon custom post type
 *
 * Pre-reqs: Custom Post Type Plugin, sermon custom post type (declared above)
 * @since 3.0.5
 */
class WP_Widget_Sermon_Archives extends WP_Widget {

	function WP_Widget_Sermon_Archives() {
		$widget_ops = array('classname' => 'widget_sermon_archive', 'description' => __( 'A monthly archive of your site&#8217;s sermons') );
		$this->WP_Widget('sermon_archives', __('Sermon Archives'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$c = $instance['count'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Sermon Archives') : $instance['title'], $instance, $this->id_base);

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		if ( $d ) {
?>
		<select name="sermon-archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo esc_attr(__('Select Month')); ?></option> <?php mbpc_get_post_type_archives('sermon',apply_filters('widget_sermon_archives_dropdown_args', array('type' => 'monthly', 'format' => 'option', 'show_post_count' => $c))); ?> </select>
<?php
		} else {
?>
		<ul>
		<?php mbpc_get_post_type_archives('sermon',apply_filters('widget_sermon_archives_args', array('type' => 'monthly', 'show_post_count' => $c))); ?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as a drop down'); ?></label>
		</p>
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Sermon_Archives");'));
?>
