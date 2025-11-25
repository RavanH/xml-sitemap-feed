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
$priority = \get_post_meta( $post_id, '_xmlsf_priority', true );

// value prechecks to prevent "invalid form control not focusable" when meta box is hidden.
$priority = \is_numeric( $priority ) ? \XMLSF\sanitize_number( $priority ) : '';

?>
<p>
	<label>
		<?php esc_html_e( 'Priority', 'xml-sitemap-feed' ); ?>
		<input type="number" step="0.1" min="0.1" max="1" name="xmlsf_priority" id="xmlsf_priority" value="<?php echo esc_attr( $priority ); ?>" class="small-text" />
	</label>
	<span class="description">
		<?php printf( /* translators: Settings (linked to wp-admin/options-general.php) */ esc_html__( 'Leave empty for automatic Priority as configured on %1$s > %2$s.', 'xml-sitemap-feed' ), esc_html__( 'Settings' ), '<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=xmlsf">' . esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</span>
</p>
<p>
	<label>
		<input type="checkbox" name="xmlsf_exclude" id="xmlsf_exclude" value="1"<?php checked( ! empty( $exclude ) ); ?> />
		<?php esc_html_e( 'Exclude from XML Sitemap', 'xml-sitemap-feed' ); ?>
	</label>
</p>
