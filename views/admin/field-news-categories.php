<?php
/**
 * Categories fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Categories' ); ?>
	</legend>
	<p>
		<?php esc_html_e( 'Limit to posts in these post categories:', 'xml-sitemap-feed' ); ?>
	</p>
	<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>
	<ul class="cat-checklist">
		<?php echo $cat_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</ul>
</fieldset>
