<?php
/**
 * Post types fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'Post types', 'xml-sitemap-feed' ); ?></legend>
<?php
foreach ( $post_types as $_post_type ) :
	$obj = get_post_type_object( $_post_type );
	if ( ! is_object( $obj ) ) {
		continue;
	}
	?>
	<label>
		<input type="<?php echo esc_attr( $type ); ?>" name="xmlsf_news_tags[post_type][]" id="xmlsf_post_type_<?php echo esc_attr( $obj->name ); ?>" value="<?php echo esc_attr( $obj->name ); ?>"<?php checked( in_array( $obj->name, $news_post_type, true ), true ) . disabled( ! in_array( $obj->name, $allowed, true ), true ); ?> />
		<?php echo esc_html( $obj->label ); ?>
	</label>
	<br/>
<?php endforeach; if ( $do_warning || 'radio' === $type ) : ?>
	<p class="description">
		<?php
		if ( $do_warning ) {
			esc_html_e( 'Custom post types that do not use the post category taxonomy, cannot be included as long as any category is selected below.', 'xml-sitemap-feed' );
		}
		?>
		<?php
		if ( 'radio' === $type ) {
			printf( /* Translators: Advanced plugin name */ esc_html__( 'Including multiple post types in the same News Sitemap is provided by the %s module.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/google-news-advanced/" target="_blank">' . esc_html__( 'Google News Advanced', 'xml-sitemap-feed' ) . '</a>' );
		}
		?>
	</p>
<?php endif; ?>
</fieldset>
