<?php
/**
 * Help tab: Post types
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong>[] <?php esc_html_e( 'Include', 'xml-sitemap-feed' ); ?>&hellip;</strong>
	<br />
	<?php esc_html_e( 'Activate this to include the post type in the sitemap index.', 'xml-sitemap-feed' ); ?>
</p>
<?php
if ( ! xmlsf_uses_core_server() ) :
	?>
<p>
	<strong><?php esc_html_e( 'Split by', 'xml-sitemap-feed' ); ?> [&hellip;]</strong>
	<br />
	<?php esc_html_e( 'Choose Split by Month if you experience errors or slow sitemaps.', 'xml-sitemap-feed' ); ?>
	<?php echo apply_filters( 'xmlsf_posttype_archive_field_description', sprintf( /* Translators: XML Sitemap Advanced */ esc_html__( 'More options available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>
<?php endif; ?>
<p>
	<strong><?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?> [&hellip;]</strong>
	<br />
	<?php esc_html_e( 'Priority can be used to signal the relative importance of post types in general and individual posts in particular.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Priority can be overridden on individual posts.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong>[] <?php esc_html_e( 'Automatic Priority calculation.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Adjusts the Priority based on factors like age, comments, sticky post or blog page.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Please note: this option can make sitemap generation slower and more resource intensive.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong>[] <?php esc_html_e( 'Update the Last Changed date on each new comment.', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'The Last Changed timestamp will be updated whenever a comment is added. Useful for sites where user interaction like comments play a large role and give added content value. But otherwise this is not advised.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Please note: this option can make sitemap generation slower and more resource intensive.', 'xml-sitemap-feed' ); ?>
</p>
<?php
if ( ! xmlsf_uses_core_server() ) :
	?>
<p>
	<strong><?php esc_html_e( 'Add image tags for', 'xml-sitemap-feed' ); ?> [&hellip;]</strong>
	<br />
	<?php esc_html_e( 'Choose which images should be added to the sitemap. Note that images can be present in a post while not being attached to that post. If you have images in your Library that are not attached to any post, or not used as featured image, then those will not be present in your sitemap.', 'xml-sitemap-feed' ); ?>
</p>
<?php endif; ?>
