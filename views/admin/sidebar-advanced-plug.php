<?php
/**
 * Sidebar: Help
 *
 * @package XML Sitemap & Google News
 */

?>
<div style="border: 4px solid #2a8c41; padding: 0 10px; background-color:lemonchiffon">
	<h3><span class="dashicons dashicons-yes-alt" style="color:#2a8c41"></span> <?php esc_html_e( 'Sitemap notifier	', 'xml-sitemap-feed' ); ?></h3>
	<p>
		<strong>
			<?php printf( /* translators: %s: Sitemap name */ esc_html__( 'Notify Google by automaticly resubmitting your %s to Google Search Console upon each new publication.', 'xml-sitemap-feed' ), esc_html( $sitemap_name ) ); ?>
			<?php
			printf(
				/* translators: %s: Advanced plugin name (linked to https://premium.status301.com/) */
				esc_html__( 'Available in %s.', 'xml-sitemap-feed' ),
				'<a href="' . esc_url( $adv_plugin_url ) . '" target="_blank">' . esc_html( $adv_plugin_name ) . '</a>'
			);
			?>
		</strong>
	</p>
	<?php if ( time() < 1764676800 ) : ?>
		<p style="padding: 5px 10px; border: red dashed;background-color: yellow;border-radius: 10px">
			<strong>
				<em>If you hurry, you might still catch our</em>
				<a href="<?php echo esc_url( $adv_plugin_url ); ?>?discount=BFCM30">Black Friday to Cyber Monday deal!</a>
			</strong>
		</p>
	<?php endif; ?>
</div>