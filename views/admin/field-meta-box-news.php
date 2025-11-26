<?php
/**
 * Meta box: News
 *
 * @package XML Sitemap & Google News
 */

// Use nonce for verification.
\wp_nonce_field( XMLSF_BASENAME, '_xmlsf_news_nonce' );

// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
$exclude  = 'private' === $post_status || \get_post_meta( $post_id, '_xmlsf_news_exclude', true );
$disabled = 'private' === $post_status;
?>
<p>
	<label>
		<input type="checkbox" name="xmlsf_news_exclude" id="xmlsf_news_exclude" value="1"<?php checked( ! empty( $exclude ) ); ?><?php disabled( $disabled ); ?> />
		<?php esc_html_e( 'Exclude from Google News Sitemap', 'xml-sitemap-feed' ); ?>
	</label>
</p>
