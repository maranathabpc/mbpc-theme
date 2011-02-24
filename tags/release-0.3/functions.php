<?php
//remove action hook for twentyten widget areas
remove_action('widgets_init','twentyten_widgets_init');

function mbpc_widgets_init() {
	twentyten_widgets_init();

	// Area 3, will show up when certain conditions are met, see sidebar.php 
	register_sidebar( array(
		'name' => __( 'Third Widget Area', 'twentyten' ),
		'id' => 'third-widget-area',
		'description' => __( 'The third widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 4, will show up when certain conditions are met, see sidebar.php 
	register_sidebar( array(
		'name' => __( 'Fourth Widget Area', 'twentyten' ),
		'id' => 'fourth-widget-area',
		'description' => __( 'The fourth widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

}

add_action('widgets_init','mbpc_widgets_init');


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

//register shortcode for year
function mbpc_year() {
	return '<span class="the-year">' . date( 'Y' ) . '</span>';
}
add_shortcode('the-year', 'mbpc_year');
//allow shortcodes to be used in text widgets
add_filter('widget_text', 'do_shortcode');

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
							'description'=>__('Contains a link to the recording of the sermon as well as the sermon summary'),
							'public'=>true,
							'menu_position'=>5,
							'has_archive' => true,
							'rewrite'=>array('with_front'=>false)
						)
						);

	register_post_type('newsletter',
						array(
							'labels'=>array(
										'name'=>__('Maranatha Messengers'),
										'singular_name'=>__('Maranatha Messenger'),
										'add_new'=>__('Add New'),
										'add_new_item'=>__('Add New Maranatha Messenger'),
										'edit'=>__('Edit'),
										'edit_item'=>__('Edit Maranatha Messenger'),
										'new_item'=>__('New Maranatha Messenger'),
										'view_item'=>__('View the Maranatha Messenger'),
										'search_items'=>__('Search Maranatha Messengers'),
										'not_found'=>__('No Maranatha Messengers found'),
										'not_found_in_trash'=>__('No weeklies found in trash')),
							'description'=>__('Download link for the Maranatha Messenger'),
							'public'=>true,
							'menu_position'=>6,
							'rewrite'=>array('with_front'=>false),
							'has_archive' => true,
							'taxonomies' => array('category')		//allows newsletter post type to use default categories
						)
						);

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

//currently not used
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
 * Originally from the Custom Post Types plugin
 * Hooks into 'getarchives_where' filter to change the WHERE constraint to support post type filtering.
 * Will replace "post_type = 'post'" with "post_type = '{POST_TYPE}'". 
 * That part will be removed if 'post_type' in $options is set to 'all'.
 * @param string $where the WHERE constraint
 * @param array $options options that are passed to the 'wp_get_archives' function
 * @return string the modified (or not) WHERE constraint
 */
function pta_wp_get_archives_filter($where, $options) {
	if(!isset($options['post_type'])) return $where; // OK - this is regular wp_get_archives call - don't do anything
	
	global $wpdb; // get the DB engine
	
	$post_type = $wpdb->escape($options['post_type']); // escape the passed value to be SQL safe
	if($post_type == 'all') $post_type = ''; // if we want to have archives for all post types
	else $post_type = "post_type = '$post_type' AND"; // otherwise just for specific one
	
	$where = str_replace('post_type = \'post\' AND', $post_type, $where);
	
	return $where;
}
add_filter('getarchives_where', 'pta_wp_get_archives_filter', 10, 2);


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
		$replacement = 'href=\'' . get_bloginfo('url') . '/' . $post_type .'/';
		
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
class WP_Widget_Custom_Post_Type_Archives extends WP_Widget {

	function WP_Widget_Custom_Post_Type_Archives() {
		$widget_ops = array('classname' => 'widget_custom_post_type_archive', 'description' => __( 'A monthly archive of your specified post type') );
		$this->WP_Widget('custom_post_type_archives', __('Custom Post Type Archives'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$c = $instance['count'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Sermon Archives') : $instance['title'], $instance, $this->id_base);
		$post_type = $instance['posttype'];

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		if ( $d ) {
?>
		<select name="custom-post-type-archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo esc_attr(__('Select Month')); ?></option> <?php mbpc_get_post_type_archives($post_type,apply_filters('widget_custom_post_type_archives_dropdown_args', array('type' => 'monthly', 'format' => 'option', 'show_post_count' => $c))); ?> </select>
<?php
		} else {
?>
		<ul>
		<?php mbpc_get_post_type_archives($post_type,apply_filters('widget_custom_post_type_archives_args', array('type' => 'monthly', 'show_post_count' => $c))); ?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '','posttype' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['posttype'] = strip_tags($new_instance['posttype']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '', 'posttype' => '') );
		$title = strip_tags($instance['title']);
		$post_type = strip_tags($instance['posttype']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('posttype'); ?>"><?php _e('Custom Post Type:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('posttype'); ?>" name="<?php echo $this->get_field_name('posttype'); ?>" type="text" value="<?php echo esc_attr($post_type); ?>" /></p>
		<p>
			<input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Show post counts'); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e('Display as a drop down'); ?></label>
		</p>
<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Custom_Post_Type_Archives");'));

//from the meta box tutorial at http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
//new code is from the multiple meta box tutorial at http://www.deluxeblogtips.com/2010/05/howto-meta-box-wordpress.html
/**************************************************************************************************************
 *  the code below adds meta boxes for the sermon and newsletter post types
 *************************************************************************************************************/

$prefix = 'mbpc_';
$meta_boxes = array();

//meta box for scripture text in sermon custom post type
$meta_boxes[] = array(
    'id' => 'scripture-text-meta',
    'title' => 'Scripture Text',
    'pages' => array('sermon'),
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Scripture Text',
            'desc' => 'Enter scripture text for this sermon',
            'id' => $prefix . 'scripture_text_text',
            'type' => 'text',
            'std' => ''
        )    
	)
);

//meta box for memory verse in newsletter (Maranatha Messenger) post type
$meta_boxes[] = array(
    'id' => 'mem-verse-text-meta',
    'title' => 'Memory Verse',
    'pages' => array('newsletter'),
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Scripture Reference',
            'desc' => 'Enter memory verse reference',
            'id' => $prefix . 'mem_verse_ref_text',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Verse Text',
            'desc' => 'Enter full text of memory verse',
            'id' => $prefix . 'mem_verse_textarea',
            'type' => 'textarea',
            'std' => ''
        )
	)
);

foreach($meta_boxes as $meta_box) {
	$my_box = new My_meta_box($meta_box);
}

class My_meta_box {

    protected $_meta_box;

    // create meta box based on given data
    function __construct($meta_box) {
        $this->_meta_box = $meta_box;
        add_action('admin_menu', array(&$this, 'add'));

        add_action('save_post', array(&$this, 'save'));
    }

    /// Add meta box for multiple post types
    function add() {
        foreach ($this->_meta_box['pages'] as $page) {
            add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
        }
    }

    // Callback function to show fields in meta box
    function show() {
        global $post;

        // Use nonce for verification
        echo '<input type="hidden" name="mbpcTheme_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
    
        echo '<table class="form-table">';

        foreach ($this->_meta_box['fields'] as $field) {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);
        
            echo '<tr>',
                    '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                    '<td>';
            switch ($field['type']) {
                case 'text':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                        '<br />', $field['desc'];
                    break;
                case 'textarea':
                    echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
                        '<br />', $field['desc'];
                    break;
                case 'select':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option) {
                        echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                    }
                    echo '</select>';
                    break;
                case 'radio':
                    foreach ($field['options'] as $option) {
                        echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                    }
                    break;
                case 'checkbox':
                    echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
                    break;
            }
            echo     '<td>',
                '</tr>';
        }
    
        echo '</table>';
    }

    // Save data from meta box
    function save($post_id) {
        // verify nonce
        if (!wp_verify_nonce($_POST['mbpcTheme_meta_box_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // check permissions
        if ('page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        foreach ($this->_meta_box['fields'] as $field) {
            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];
    
            if ($new && $new != $old) {
                update_post_meta($post_id, $field['id'], $new);
            } elseif ('' == $new && $old) {
                delete_post_meta($post_id, $field['id'], $old);
            }
        }
    }
}
/*****  End code for meta boxes **********************/

/**
 * Categories/Taxonomies widget class
 * Originally taken from default widgets. Modified to accept an additional parameter for taxonomy 
 *
 * @since 2.8.0
 */
class WP_Widget_Custom_Taxonomies extends WP_Widget {

	function WP_Widget_Custom_Taxonomies() {
		$widget_ops = array( 'classname' => 'widget_custom_taxonomies', 'description' => __( "A list or dropdown of custom taxonomy terms" ) );
		$this->WP_Widget('custom_taxonomies', __('Custom Taxonomy Terms'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Categories' ) : $instance['title'], $instance, $this->id_base);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$tax = $instance['taxonomy'];

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h,'taxonomy' => $tax);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			wp_dropdown_categories(apply_filters('widget_categories_dropdown_args', $cat_args));
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo home_url(); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories(apply_filters('widget_categories_args', $cat_args));
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['taxonomy'] = strip_tags($new_instance['taxonomy']);
		$instance['count'] = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown'] = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'taxonomy' => '') );
		$title = esc_attr( $instance['title'] );
		$tax = esc_attr( $instance['taxonomy']);
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e( 'Taxonomy:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('taxonomy'); ?>" name="<?php echo $this->get_field_name('taxonomy'); ?>" type="text" value="<?php echo $tax; ?>" /></p>

		<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Show as dropdown' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show post counts' ); ?></label><br />

		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
<?php
	}

}
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_Custom_Taxonomies");'));

?>
