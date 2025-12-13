<?php
/**
 * Sitemap notifier field
 *
 * @package XML Sitemap & Google News
 */

$notifier = \get_option( 'xmlsf_sitemap_notifier' );
?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Sitemap notifier', 'xml-sitemap-feed' ); ?>
	</legend>
	<label>
		<input type="checkbox" name="xmlsf_sitemap_notifier" id="xmlsf_sitemap_notifier" value="1"<?php checked( $notifier ); ?><?php disabled( apply_filters( 'xmlsf_advanced_enabled', false ), false ); ?> />
		<?php esc_html_e( 'Enable automatic sitemap submission', 'xml-sitemap-feed' ); ?>
	</label>

	<p class="description">
		<?php esc_html_e( 'Notify search engines by automaticly resubmitting your sitemap index upon each new publication.', 'xml-sitemap-feed' ); ?>
		<?php apply_filters( 'xmlsf_advanced_enabled', false ) || printf( /* Translators: %s: XML Sitemap Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' ); ?>
		<?php
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			?>
			<br>
			<span style="color:red" class="warning">
				<?php esc_html_e( 'Warning: The sitemap notifier depends on internal WordPress events but you seem to have WP Cron disabled. Make sure that you are using a reliable alternative to WP Cron, like a server cron job, to trigger events and that this is done on fairly short interval, e.g. once every minute. If the interval is longer, automatic notifications will suffer longer delays.', 'xml-sitemap-feed' ); ?>
			</span>
		<?php } ?>
	</p>
</fieldset>
