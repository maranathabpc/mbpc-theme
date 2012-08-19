<?php
/**
 * RSS2 Feed Template for displaying RSS2 Posts feed.
 *
 * @package WordPress
 */

header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
$more = 1;

echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
	<?php do_action('rss2_ns'); ?>
>

<channel>
	<title><?php bloginfo_rss('name'); wp_title_rss(); ?></title>
	<link><?php bloginfo_rss('url') ?></link>
	<description><?php bloginfo_rss("description") ?></description>
	<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false); ?></lastBuildDate>
	<language><?php bloginfo_rss( 'language' ); ?></language>
	<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
	<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
	<itunes:subtitle>Recorded sermons from MBPC</itunes:subtitle>
	<?php do_action('rss2_head'); ?>
	<?php while( have_posts()) : the_post(); ?>
	<item>
		<title><?php the_title_rss() ?></title>

		<itunes:author>
		<?php
		//get the speaker
		$authors = get_the_terms( get_the_ID(), 'speaker' ); 
		foreach( $authors as $author ) {
			echo $author->name;
		} ?>
		</itunes:author>

		<itunes:subtitle>
		<?php
		//get the scripture text
		$scrip_text = get_post_meta( get_the_ID(), 'mbpc_scripture_text_text', true ); 
		echo $scrip_text;
		?>
		</itunes:subtitle>

		<link><?php the_permalink_rss() ?></link>
		<?php the_category_rss('rss2') ?>

		<guid isPermaLink="false"><?php the_guid(); ?></guid>
		<?php //get size of attached mp3 file
		$children = get_children(array('post_parent' => $post->ID, 'post_type' => 'attachment', 'post_mime_type' => 'audio/mpeg' ));
		foreach( $children as $child ) {
			//there should only be 1 attached mp3 per post
			$size = filesize( get_attached_file( $child->ID ) );
			echo '<enclosure url="' . $child->guid . '" length="' . $size . '" type="audio/mpeg" />';
			echo '<guid>' . $child->guid . '</guid>';
		} ?>
		<pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>

<?php rss_enclosure(); ?>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php endwhile; ?>
</channel>
</rss>
