<?php
/**
 * Admin notice: Advanced compatibility message
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-warning fade is-dismissible">
	<p>
		<strong><?php esc_html_e( 'XML Sitemap & Google News', 'xml-sitemap-feed' ); ?></strong>
	</p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
		<input type="hidden" name="xmlsf-dismiss" value="xmlsf_advanced_news" />
		<input type="submit" class="button button-small alignright" name="xmlsf-dismiss-submit" value="<?php echo esc_html( translate( 'Dismiss' ) ); ?>" />
	</form>
	<?php include XMLSF_DIR . '/views/admin/section-advanced-compat-message.php'; ?>
</div>
