<?php
/**
 * The template for displaying Tag Archive pages.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php //$speaker_name = get_terms('speaker','fields=names&slug='.$term); 
				$term = get_term_by('slug',get_query_var('term'),get_query_var('taxonomy'));?>
				<h1 class="page-title"><?php
				printf( __( 'Sermons by %s', 'twentyten' ), '<span>' . $term->name . '</span>' );
				?></h1>

<?php
/* Run the loop for the tag archive to output the posts
 * If you want to overload this in a child theme then include a file
 * called loop-tag.php and that will be used instead.
 */
 get_template_part( 'loop', 'sermons' );
?>
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
