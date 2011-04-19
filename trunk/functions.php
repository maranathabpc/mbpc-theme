<?php

load_theme_textdomain('mbpctheme', STYLESHEETPATH . '/languages');

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
							'capability_type' => 'sermon',
							'map_meta_cap' => true,
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
							'capability_type' => 'newsletter',
							'map_meta_cap' => true,
							'taxonomies' => array('category'),		//allows newsletter post type to use default categories
							'supports' => array('title')			//so the editor window isn't show
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
			'hierarchical'=>true,
			'capabilities' => array('assign_terms' => 'edit_sermons')
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
			'hierarchical'=>true,
			//allows custom role to assign taxonomy terms when given the capablity to edit it
			'capabilities' => array('assign_terms' => 'edit_sermons')
			)
		);

}

/* remove menu items for church_cm role
 * assumes 'edit_others_sermons' capability only belongs to church_cm
 * and 'update_core' will only be given to admin.
 * this removes the Posts section from the menu.
*/
function mbpc_remove_menu_items() {
	if ( current_user_can( 'edit_others_sermons' ) && !current_user_can( 'update_core') ) {

		global $menu;
		$restricted = array(__('Posts'));
		end ($menu);
		while (prev($menu)){
			$value = explode(' ',$menu[key($menu)][0]);
			if(in_array($value[0] != NULL?$value[0]:"" , $restricted)) {
				unset($menu[key($menu)]);
			}
		}
	}

	//hide sermons and newsletters from blogger
	if ( current_user_can( 'edit_posts' ) && !current_user_can( 'edit_others_posts') && !current_user_can( 'edit_sermons' )) {

		global $menu;

		//don't use full name for Maranatha Messengers because the explode function later
		//will mess things up. Thus only the 1st word is necesssary.
		$restricted = array(__('Sermons'), __('Maranatha'), __('Tools'), __('Links'));
		end ($menu);
		while (prev($menu)){
			$value = explode(' ',$menu[key($menu)][0]);
			if(in_array($value[0] != NULL?$value[0]:"" , $restricted)) {
				unset($menu[key($menu)]);
			}
		}
	}

}

add_action('admin_menu', 'mbpc_remove_menu_items');

//override the posted on function in the parent theme because we don't want the author name
function twentyten_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s', 'mbpctheme' ),
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


/*
 * Adapted from Custom Post Types plugin
 * Need to add rewrite rules for the custom post types so the permalink
 * shows as site.com/{post-type}/{year}/{month}/
 */

function mbpc_register_post_type_rewrite_rules($wp_rewrite) {

	$args = array('public' => true, '_builtin' => false);	//get all public custom post types
	$output = 'names';
	$operator = 'and';
	$post_types = get_post_types($args,$output,$operator);

	$url_base = ($url_base == '') ? $url_base : $url_base . '/';
	$custom_rules = array();

	$post_types = implode('|', $post_types);
		$custom_rules = array( "$url_base($post_types)/([0-9]+)/([0-9]{1,2})/([0-9]{1,2})/?$" =>
				'index.php?post_type_index=1&post_type=' . $wp_rewrite->preg_index(1) . '&year=' . $wp_rewrite->preg_index(2) . '&monthnum=' . $wp_rewrite->preg_index(3) . '&day=' . $wp_rewrite->preg_index(4),		//year month day

								"$url_base($post_types)/([0-9]+)/([0-9]{1,2})/?$"  =>
				'index.php?post_type_index=1&post_type=' . $wp_rewrite->preg_index(1) . '&year=' . $wp_rewrite->preg_index(2) . '&monthnum=' . $wp_rewrite->preg_index(3),			//year month
								"$url_base($post_types)/([0-9]+)/?$" =>
				'index.php?post_type_index=1&post_type=' . $wp_rewrite->preg_index(1) . '&year=' . $wp_rewrite->preg_index(2)	//year
							);

	$wp_rewrite->rules = array_merge($custom_rules, $wp_rewrite->rules); // merge existing rules with custom ones
	
	return $wp_rewrite;
}

add_filter('generate_rewrite_rules', 'mbpc_register_post_type_rewrite_rules', 100);


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
		$pattern = get_bloginfo('url') . '/blog/';
		$replacement = get_bloginfo('url') . '/' . $post_type .'/';
		
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
 * Pre-reqs: Custom Post Type Plugin, any post type (declared above)
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

//meta box for uploading the newsletter (Maranatha Messenger)
$meta_boxes[] = array(
	'id' => 'newsletter-upload',
	'title' => 'Newsletter Upload',
	'pages' => array('newsletter'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Newsletter Upload',
			'desc' => 'Filename format: ' . __('mm-yyyy-mm-dd.pdf', 'mbpctheme'),
			'id' => $prefix . 'newsletter_file',
			'type' => 'file',
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

$meta_boxes[] = array(
	'id' => 'sermon-audio-upload',
	'title' => 'Sermon Audio Upload',
	'pages' => array( 'sermon' ),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
			'name' => 'Sermon Audio File',
			'desc' => 'Filename format: ' . __('sermon-yyyy-mm-dd.mp3', 'mbpctheme'),
			'id' => $prefix . 'sermon_file',
			'type' => 'file',
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
		
		// fix upload bug: http://www.hashbangcode.com/blog/add-enctype-wordpress-post-and-page-forms-471.html
		$upload = false;
		foreach ($meta_box['fields'] as $field) {
			if ($field['type'] == 'file' || $field['type'] == 'image') {		//no support in the rest of this code for images
				$upload = true;
				break;
			}
		}
		$current_page = substr(strrchr($_SERVER['PHP_SELF'], '/'), 1, -4);
		if ($upload && ($current_page == 'page' || $current_page == 'page-new' || $current_page == 'post' || $current_page == 'post-new')) {
			add_action('admin_head', array(&$this, 'add_post_enctype'));
			//add_action( 'admin_head', array( &$this, 'hide_editor' ) );
		}
		//hide editor if adding a new post of sermon type
		if( isset( $_GET['post_type'] ) && 'sermon' == $_GET['post_type'] ) {
			add_action( 'admin_head', array( &$this, 'hide_editor' ) );
		}
		//hide editor if editing a post which is a sermon type
		if( isset( $_GET['post'] ) && 'sermon' == get_post_type( $_GET['post'] ) ) {
			add_action( 'admin_head', array( &$this, 'hide_editor' ) );
		}

		add_action('admin_menu', array(&$this, 'add'));
        add_action('save_post', array(&$this, 'save'));
    }

	function hide_editor() {
		?>
			<style>
				#editor-toolbar { display: none; }
				#editorcontainer {display: none; }
				#quicktags {display:none;}
				#post-status-info {display:none;}
			</style>
		<?php
	}

	function add_post_enctype() {
		echo '
			<script type="text/javascript">
			jQuery(document).ready(function(){
					jQuery("#post").attr("enctype", "multipart/form-data");
					jQuery("#post").attr("encoding", "multipart/form-data");
					});
		</script>';
	}

    /// Add meta box for multiple post types
    function add() {
		//sets context and priority if left unset in metabox declarations
		$this->_meta_box['context'] = empty($this->_meta_box['context']) ? 'normal' : $this->_meta_box['context'];
		$this->_meta_box['priority'] = empty($this->_meta_box['priority']) ? 'high' : $this->_meta_box['priority'];

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
				case 'file':
					//find any attachments assigned to the post
					$children = get_children(array('post_parent' => $post->ID, 'post_type' => 'attachment'));
					if($children) {
						echo 'Currently attached: <br/>';
						foreach ($children as $child) {		//there should only be 1 child anyway
							echo '<a href=\'' . $child->guid . '\'>' . $child->guid . '</a><br />';
						}
						echo '<br/>';
					}
					echo $meta ? "$meta<br />" : '', '<input type="file" name="', $field['id'], '" id="', $field['id'], '" />',
						 '<br />', $field['desc'];
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
			$name = $field['id'];

            $old = get_post_meta($post_id, $field['id'], true);
            $new = $_POST[$field['id']];

			if ($field['type'] == 'file' || $field['type'] == 'image') {
				if (!empty($_FILES[$name])) {
					$this->fix_file_array($_FILES[$name]);
					//get the name of the uploaded file 
					$uploaded_file = $_FILES[$name]['name'];
					$uploaded_file = basename($uploaded_file);
					//replace spaces with '-' to facilitate year/mth extraction
					$uploaded_file = str_replace(' ', '-', $uploaded_file);		
					$name_parts = explode('-', $uploaded_file);

					//check if it's a chinese file, they should all have chi as the prefix
					$pos = strpos( strtoupper( $name_parts[0] ), 'CHI' );
					
					//english sermon or newsletter
					if ( $pos === false ) {
						if( 'WEEKLY' == strtoupper( $name_parts[0] ) ) {		//rename weekly to mm
							$name_parts[0] = 'mm';
						}
						$name_parts[0] = strtolower( $name_parts[0] );		//convert prefix to lowercase
					}
					else {			//chinese sermon or newsletter
						if ( count( $name_parts ) == 5 ) {		//Chinese <type> yyyy mm dd.<extension>
							//final filename will be chi-mm-yyyy-mm-dd.pdf
							if ( 'MM' == strtoupper( $name_parts[1] ) )
								$name_parts[0] = 'chi-mm';
							//final filename will be chi-sermon-yyyy-mm-dd.mp3
							elseif ( 'SERMON' == strtoupper( $name_parts[1] ) )
								$name_parts[0] = 'chi-sermon';

							unset( $name_parts[1] );
							$name_parts = array_values( $name_parts );		//re-index to 4 part filename
						}

					}
					
					//assumes 4 part filenames for date operations
					if( !is_numeric( $name_parts[2] ) ) {	//assume 3 letter month, replace with 2 digit number
						$month_names = array("JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP",
												"OCT", "NOV", "DEC");
						$name_parts[2] = strtoupper($name_parts[2]);
						$name_parts[2] = array_search($name_parts[2], $month_names) + 1;
						$name_parts[2] = sprintf('%02u', $name_parts[2]);
					}

					$last_part = explode('.', $name_parts[3]);			//split file extension and day
					$last_part[0] = sprintf('%02u', $last_part[0]);		//convert day to 2 digit format
					$name_parts[3] = implode('.', $last_part);			//combine day and extension

					$time = $name_parts[1] . '-' . $name_parts[2];		//time is in yyyy-mm format
					if(preg_match('/[0-9]{4}-[0-1][0-9]/', $time) == 0) {		//time does not match format
						$time = null;
					}
					$_FILES[$name]['name'] = implode('-', $name_parts);			//rename file

					$file = wp_handle_upload($_FILES[$name], array('test_form' => false), $time );
					$filename = $file['url'];
					if (!empty($filename)) {
						$currPost = get_post($post_id);

						//unattach current attachment, if any
						//only unattach files with the same prefix as the uploaded one
						$children = get_children(array('post_parent' => $post_id, 'post_type' => 'attachment'));
						if($children) {
							foreach($children as $child) {
								$path_parts = pathinfo($child->guid);
								//check if prefix of uploaded file matches prefix of file to be unattached
								$child_parts = explode( '-', $path_parts['filename']);
								//chinese attachments have 5 elements when exploded, need to concat the 1st 2 with a '-' in between
								if( $child_parts[0] == $name_parts [0] || ( ( $child_parts[0].'-'.$child_parts[1] ) == $name_parts[0] ) ) {
									wp_update_post(array('ID' => $child->ID, 'post_parent' => 0, 
												'post_name' => $path_parts['filename'], 
												'post_title' => $path_parts['filename']));
								}
							}
						}

						//attach attachment post to the main parent post
						$wp_filetype = wp_check_filetype(basename($filename), null);
						$attachment = array(
								'post_mime_type' => $wp_filetype['type'],
								//	'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
								'post_status' => 'inherit',
								'guid' => $filename,
								'post_title' => $currPost -> post_title
								);
						$attach_id = wp_insert_attachment($attachment, $filename, $post_id);
						// you must first include the image.php file
						// for the function wp_generate_attachment_metadata() to work
						require_once(ABSPATH . 'wp-admin/includes/image.php');
						$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
						wp_update_attachment_metadata($attach_id, $attach_data);

						//update post content with the download text and link to the file
						//add post content only if it's a sermon type
						$pos = strpos ( strtoupper( $name_parts[0] ), 'SERMON' );
						if( $pos !== false ) {
							$post_content = '[audio:' . $filename . '|titles=' . $currPost -> post_title . ']';
							$post_content .= '<p>Download MP3: <a href="' . $filename . '">' . $currPost -> post_title . '</a></p>';
							wp_update_post(array('ID' => $post_id, 'post_content' => $post_content));
						}
					}
				}
			}

			if ($new && $new != $old /*&& $field['type'] != 'file'*/) {
				update_post_meta($post_id, $field['id'], $new);
			} elseif ('' == $new && $old && $field['type'] != 'file' && $field['type'] != 'image') {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}


	/**
	 * Fixes the odd indexing of multiple file uploads from the format:
	 *
	 * $_FILES['field']['key']['index']
	 *
	 * To the more standard and appropriate:
	 *
	 * $_FILES['field']['index']['key']
	 *
	 * @param array $files
	 * @author Corey Ballou
	 * @link http://www.jqueryin.com
	 */
	function fix_file_array(&$files) {
		$names = array(
			'name' => 1,
			'type' => 1,
			'tmp_name' => 1,
			'error' => 1,
			'size' => 1
		);

		foreach ($files as $key => $part) {
			// only deal with valid keys and multiple files
			$key = (string) $key;
			if (isset($names[$key]) && is_array($part)) {
				foreach ($part as $position => $value) {
					$files[$position][$key] = $value;
				}
				// remove old key reference
				unset($files[$key]);
			}
		}
	}
	
}
/*****  End code for meta boxes **********************/

/***** add filter for wp_get_attachment_url() to handle different output for subsites *******/

add_filter( 'wp_get_attachment_url', 'mbpc_wp_get_attachment_url_filter', 10, 2);

function mbpc_wp_get_attachment_url_filter($url, $postID) {
	$upload_dir = wp_upload_dir();

	//search through the url and see if it contains a 2nd instance of the baseurl
	$pos = strpos( $url, $upload_dir['baseurl'] , 1);

	if ( $pos !== false) {		//2nd instance exists
		$url = substr ($url, $pos);
		//update post meta so it won't have to find the substring on subsequent calls
		update_post_meta( $postID, '_wp_attached_file', substr( $url, strlen( $upload_dir['baseurl'] ) + 1 ));
	}
	return $url;
}

/*****   End code for wp_get_attachment_url filter **************/


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
