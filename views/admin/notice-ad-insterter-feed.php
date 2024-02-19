<?php
/**
 * Admin notice: Add Inserter feed
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-error fade is-dismissible">
	<p>
		<?php
		printf( /* Translators: RSS Feed (plugin option name), Plugin name, plugin settings page (linked) */
			esc_html__( 'The option %1$s in %2$s is not compatible with %3$s. Please disable it under the %4$s tab of each active ad block.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'RSS Feed', 'ad-inserter' ) . '</strong>',
			'<a href="' . esc_url( admin_url( 'options-general.php' ) ) . '?page=ad-inserter.php">' . esc_html__( 'Ad Inserter', 'ad-inserter' ) . '</a>',
			esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ),
			esc_html__( 'Misc', 'ad-inserter' )
		);
		?>
	</p>
</div>
