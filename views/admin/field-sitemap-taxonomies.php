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
		<?php esc_html_e( 'Limit to these taxonomies:', 'xml-sitemap-feed' ); ?>
	</p>
	<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>
	<ul class="cat-checklist">
			<?php
			foreach ( $public_tax as $name ) {
				$label = get_taxonomy( $name )->label;
				?>
		<li>
			<label>
				<input type="checkbox" name="xmlsf_taxonomies[]" id="xmlsf_taxonomies_<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $name ); ?>" <?php checked( in_array( $name, (array) $taxonomies, true ) ); ?>/>
				<?php echo esc_html( $label ); ?> (<?php echo esc_html( wp_count_terms( $name ) ); ?>)
			</label>
		</li>
		<?php } ?>
	</ul>
</fieldset>
