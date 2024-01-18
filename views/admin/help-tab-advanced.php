<?php
/**
 * Help tab: Advanced
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<strong><?php esc_html_e( 'External web pages', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Add the full URL, including protocol (http/https) and domain.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Optionally add a priority value between 0 and 1, separated with a space after the URL.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Start each URL on a new line.', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<strong><?php esc_html_e( 'External XML Sitemaps', 'xml-sitemap-feed' ); ?></strong>
	<br />
	<?php esc_html_e( 'Add the full URL, including protocol (http/https) and domain.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Start each URL on a new line.', 'xml-sitemap-feed' ); ?>
	<br>
	<span style="color: red" class="warning">
		<?php esc_html_e( 'Only valid sitemaps are allowed in the Sitemap Index. Use your Webmaster Tools account to verify!', 'xml-sitemap-feed' ); ?>
	</span>
</p>
