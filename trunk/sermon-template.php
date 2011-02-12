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

query_posts(array('post_type'=>'sermon','posts_per_page'=>2,'paged'=>get_query_var('paged')));
?>

<?php

 get_template_part( 'loop', 'sermons' );?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
