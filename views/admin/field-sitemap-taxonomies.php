<?php
/**
 * Taxonomies fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_taxonomies">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Taxonomies', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<?php esc_html_e( 'Include these taxonomies, or select none to automaticly include all public taxonomies:', 'xml-sitemap-feed' ); ?>
	</p>
	<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>
	<ul class="cat-checklist">
	<?php
	foreach ( $public_tax as $obj ) {
		$count = wp_count_terms( $obj->name );
		// Adjust the count for Post Formats (to exclude the Standard type).
		if ( 'post_format' === $obj->name ) {
			--$count;
		}
		?>
		<li>
			<label>
				<input type="checkbox" name="xmlsf_taxonomies[]" id="xmlsf_taxonomies_<?php echo esc_attr( $obj->name ); ?>" value="<?php echo esc_attr( $obj->name ); ?>" <?php checked( in_array( $obj->name, (array) $taxonomies, true ) ); ?>/>
				<?php echo esc_html( $obj->label ); ?> (<?php echo esc_html( $count ); ?>)
			</label>
		</li>
		<?php
	}
	?>
	</ul>
</fieldset>
