<?php
/**
 * The Sidebar containing the primary and secondary widget areas.
 * Modified for the mbpcTheme so the widget areas shown will differ
 * depending on the page
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

<?php 
	wp_reset_query();
	if( 'sermon' == get_post_type() || is_tax() || is_page_template('sermon-template.php')) : ?>

		<div id="primary" class="widget-area" role="complementary">
			<ul class="xoxo">

<?php
	/* When we call the dynamic_sidebar() function, it'll spit out
	 * the widgets for that widget area. If it instead returns false,
	 * then the sidebar simply doesn't exist, so we'll hard-code in
	 * some default sidebar stuff just in case.
	 */
	if ( ! dynamic_sidebar( 'primary-widget-area' ) ) : ?>
	
			<li id="search" class="widget-container widget_search">
				<?php get_search_form(); ?>
			</li>

			<li id="archives" class="widget-container">
				<h3 class="widget-title"><?php _e( 'Archives', 'twentyten' ); ?></h3>
				<ul>
					<?php wp_get_archives( 'type=monthly' ); ?>
				</ul>
			</li>

			<li id="meta" class="widget-container">
				<h3 class="widget-title"><?php _e( 'Meta', 'twentyten' ); ?></h3>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<?php wp_meta(); ?>
				</ul>
			</li>

		<?php endif; // end primary widget area ?>
			</ul>
		</div><!-- #primary .widget-area -->

<?php elseif( is_category() || is_page_template('newsletter-template.php') || 'newsletter' == get_post_type()) :
	// A second sidebar for widgets, just because.
	if ( is_active_sidebar( 'secondary-widget-area' ) ) : ?>

		<div id="secondary" class="widget-area" role="complementary">
			<ul class="xoxo">
				<?php dynamic_sidebar( 'secondary-widget-area' ); ?>
			</ul>
		</div><!-- #secondary .widget-area -->

<?php endif; //end secondary widget area ?>

<?php elseif( is_category() || is_page_template( 'qna-template.php' ) || 'qna' == get_post_type() ) :
	//widget area for qna pages
	if ( is_active_sidebar( 'fifth-widget-area' ) ) : ?>

		<div id="fifth-widget-area" class="widget-area" role="complementary">
			<ul class="xoxo">
				<?php dynamic_sidebar( 'fifth-widget-area' ); ?>
			</ul>
		</div><!-- #fifth-widget .widget-area -->

<?php endif; //end fifth widget area ?>

<?php elseif(is_page()) :
	//Third widget area, for pages
	if(is_active_sidebar('third-widget-area')) : ?>
		<div id="third-widget-area" class="widget-area" role="complementary">
			<ul class="xoxo">
				<?php dynamic_sidebar('third-widget-area'); ?>
			</ul>
		</div><!-- #third-widget .widget-area -->
<?php endif;  //end third widget area ?>

<?php else :
	//Fourth widget area, for posts
	if(is_active_sidebar('fourth-widget-area')) : ?>
		<div id="fourth-widget-area" class="widget-area" role="complementary">
			<ul class="xoxo">
				<?php dynamic_sidebar('fourth-widget-area'); ?>
			</ul>
		</div><!-- #fourth-widget .widget-area -->
<?php endif;  //end fourth widget area ?>
<?php endif; ?>
