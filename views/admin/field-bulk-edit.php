<?php
/**
 * Bulk Edit: Sitemap
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset class="inline-edit-col-right">
	<div class="inline-edit-col column-<?php echo esc_attr( $column_name ); ?>">
		<label class="inline-edit-group">
			<?php esc_html_e( 'XML Sitemap', 'xml-sitemap-feed' ); ?>
			<select name="xmlsf_exclude">
				<option value="-1"><?php esc_html_e( '&mdash; No Change &mdash;' ); ?></option>
				<option value="1"<?php disabled( $disabled ); ?>><?php esc_html_e( 'Exclude', 'xml-sitemap-feed' ); ?></option>
				<option value="0"<?php disabled( $disabled ); ?>><?php esc_html_e( 'Include', 'xml-sitemap-feed' ); ?></option>
			</select>
			<?php
			if ( $disabled ) {
				printf( /* Translators: XML Sitemap Advanced */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' );
			}
			?>
		</label>
	</div>
</fieldset>
