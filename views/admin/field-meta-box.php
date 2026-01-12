<?php
/**
 * Meta box: Sitemap
 *
 * @package XML Sitemap & Google News
 */

// Use nonce for verification.
\wp_nonce_field( XMLSF_BASENAME, '_xmlsf_nonce' );

// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
$exclude  = \get_post_meta( $post_id, '_xmlsf_exclude', true );

?>
<p>
	<label>
		<input type="checkbox" name="xmlsf_exclude" id="xmlsf_exclude" value="1"<?php checked( ! empty( $exclude ) ); ?> />
		<?php esc_html_e( 'Exclude from XML Sitemap', 'xml-sitemap-feed' ); ?>
	</label>
</p>
