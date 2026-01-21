<?php
/**
 * Hierarchical post types field
 *
 * @package XML Sitemap & Google News
 */

global $wp_taxonomies;

$news_options = get_option( 'xmlsf_news_tags' );
$_post_type   = isset( $news_options['post_type'] ) && ! empty( $news_options['post_type'] ) ? (array) $news_options['post_type'] : array( 'post' );
$taxonomies   = array( 'gn_keywords' => __( 'Keywords', 'xml-sitemap-feed' ) );
$disabled     = array_merge( xmlsf()->disabled_taxonomies(), array( 'stock_tickers' ) );

// build intersection array of tax objects.
foreach ( $_post_type as $_type ) {
	$tax_intersect = isset( $tax_intersect ) ? array_intersect( $tax_intersect, get_object_taxonomies( $_type ) ) : get_object_taxonomies( $_type );
}

foreach ( $tax_intersect as $tax_name ) {
	// check each tax public flag and append name to array.
	if ( is_object( $wp_taxonomies[ $tax_name ] ) && ! empty( $wp_taxonomies[ $tax_name ]->show_ui ) && ! in_array( $tax_name, $disabled, true ) ) {
		$taxonomies[ $tax_name ] = $wp_taxonomies[ $tax_name ]->label;
	}
}

$adv_options   = get_option( 'xmlsf_news_advanced' );
$keywords_from = ! empty( $adv_options['keywords_from'] ) ? $adv_options['keywords_from'] : '';
?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Keywords', 'xml-sitemap-feed' ); ?>
	</legend>
	<label><?php esc_html_e( 'Use keywords from', 'xml-sitemap-feed' ); ?>
		<select name="xmlsf_news_advanced[keywords_from]">
			<option value=""><?php echo esc_html( translate( 'None' ) );  // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></option>
<?php foreach ( $taxonomies as $name => $label ) { ?>
			<option value="<?php echo esc_attr( $name ); ?>"<?php selected( $keywords_from === $name, true ); ?><?php disabled( ! apply_filters( 'xmlsf_news_advanced_enabled', false ) ); ?>><?php echo esc_html( $label ); ?></option>
<?php } ?>
		</select>
	</label>
	<p class="description">
		<?php apply_filters( 'xmlsf_news_advanced_enabled', false ) || printf( /* Translators: %s: Google News Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
	</p>
</fieldset>
