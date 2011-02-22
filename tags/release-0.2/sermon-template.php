<?php
/**
 * Template Name: Page of Sermons
 *
 * Selectable from a dropdown menu on the edit page screen.
 */
?>

<?php get_header(); ?>

		<div id="container">
			<div id="content">
<?php 
global $wp_query;
$existing = $wp_query->query;

//if on sermons display page, change the whole query
if(isset($existing['pagename']))
	$args = array('post_type'=>'sermon','posts_per_page'=>5,'paged'=>get_query_var('paged'));
//if it's a date archive, add the parameters on to the existing query with the month/yr info
//there should only be 4 sermons/mth, so no need to limit the posts per page
//in WP 3.0.5, pagination doesn't work for custom post type archives even with the plugin
else
	$args = array_merge($wp_query->query,array('post_type'=>'sermon','paged'=>get_query_var('paged')));
query_posts($args);
?>

<?php

 get_template_part( 'loop', 'sermons' );?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
