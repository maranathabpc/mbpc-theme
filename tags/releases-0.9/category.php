<?php
/**
 * The template for displaying Category Archive pages.
 * 
 * Modified in mbpcTheme so both the custom newsletter post type
 * and normal posts will show up as they're both using the built-in
 * categories.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<h1 class="page-title"><?php
					printf( __( 'Category Archives: %s', 'twentyten' ), '<span>' . single_cat_title( '', false ) . '</span>' );
				?></h1>
				<?php
					global $wp_query;
					$args = array_merge($wp_query->query,array('post_type'=>array('post','newsletter'),'paged'=>get_query_var('paged')));
					query_posts($args);

					$category_description = category_description();
					if ( ! empty( $category_description ) )
						echo '<div class="archive-meta">' . $category_description . '</div>';

				/* Run the loop for the category page to output the posts.
				 *  Overridden in child theme. Using the newsletter loop
				 *  so memory verses will be displayed.
				 */
				get_template_part( 'loop', 'newsletters' );
				?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
