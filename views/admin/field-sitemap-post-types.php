<?php
/**
 * Taxonomies fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_post_types">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Post types', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<?php esc_html_e( 'Include these post types, or select none to automaticly include all public post types:', 'xml-sitemap-feed' ); ?>
	</p>
	<!--<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>-->
	<ul>
		<?php
		$pt_objects = get_post_types( array( 'public' => true ), 'objects' );
		foreach ( $pt_objects as $name => $pt_obj ) {
			if ( in_array( $name, xmlsf()->disabled_post_types(), true ) || ! is_post_type_viewable( $pt_obj ) ) {
				continue;
			}
			?>
		<li>
			<label>
				<input type="checkbox" name="xmlsf_post_types[]" id="xmlsf_post_types_<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( in_array( $name, (array) $post_types, true ) ); ?>/>
				<?php echo esc_html( $pt_obj->label ); ?>
			</label>
		</li>
		<?php } ?>
	</ul>
</fieldset>
