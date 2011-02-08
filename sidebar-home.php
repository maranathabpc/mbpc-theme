<?php
/**
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

		<div id="primary" class="widget-area" role="complementary">

<?php 
	//remove automatic <p> tags on each new line
	remove_filter('the_content','wpautop');

	//just show the table of weekly activities
	query_posts ('pagename=home-sidebar');
	while(have_posts()) {
		the_post();
		the_content();
	}
	wp_reset_query();
	
	
?>


</div>


