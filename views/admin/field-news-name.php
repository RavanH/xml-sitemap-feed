<?php
/**
 * News source name field
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset>
	<legend class="screen-reader-text"><?php esc_html_e( 'Publication name', 'xml-sitemap-feed' ); ?></legend>
	<input type="text" name="xmlsf_news_tags[name]" id="xmlsf_news_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text">
	<p class="description">
		<?php printf( /* translators: Site Title linked to Options > General */ esc_html__( 'By default, the general %s setting will be used.', 'xml-sitemap-feed' ), '<a href="options-general.php">' . esc_html__( 'Site Title' ) . '</a>' ); ?>
	</p>
</fieldset>
