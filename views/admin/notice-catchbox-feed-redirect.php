<?php
/**
 * Admin notice: Catch Box theme feed redirect
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-error fade is-dismissible">
	<p>
		<?php
		printf( /* Translators: Feed Redirect URL (Theme option name), Plugn name, Theme Options, Customizer (linked to Customizer page) */
			esc_html__( 'The Catch Box theme option %1$s is not compatible with %2$s. Please go to %3$s in the %4$s and remove it.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Feed Redirect URL', 'catch-box' ) . '</strong>',
			esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Theme Options', 'catch-box' ) . '</strong>',
			'<a href="' . esc_url( admin_url( 'customize.php' ) ) . '" target="_blank">' . esc_html( translate( 'Customizer' ) ) . '</a>'
		);
		?>
	</p>
</div>
