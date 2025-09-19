<?php
/**
 * Sidebar: Contribute
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><span class="dashicons dashicons-thumbs-up"></span> <?php esc_html_e( 'Contribute', 'xml-sitemap-feed' ); ?></h3>
<p>
	<a target="_blank" href="https://www.paypal.com/donate/?hosted_button_id=5UVXZVN5HDKBS"
		title="<?php printf( /* translators: Plugin name */ esc_html__( 'Donate to keep the free %s plugin development & support going!', 'xml-sitemap-feed' ), esc_html__( 'XML Sitemap & Google News', 'xml-sitemap-feed' ) ); ?>">
		<img src="<?php trailingslashit( plugins_url( 'assets', XMLSF_BASENAME ) ); ?>donate.png" style="border:none;float:right;margin:4px 0 0 10px" width="92" height="26" />
		<img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" style="border:none;float:right;margin:4px 0 0 10px" width="92" height="26" />
	</a>
	<div id="donate-button-container">
<div id="donate-button"></div>
<script src="https://www.paypalobjects.com/donate/sdk/donate-sdk.js" charset="UTF-8"></script>
<script>
PayPal.Donation.Button({
env:'production',
hosted_button_id:'5UVXZVN5HDKBS',
image: {
src:'https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif',
alt:'Donate with PayPal button',
title:'PayPal - The safer, easier way to pay online!',
}
}).render('#donate-button');
</script>
</div>
	<?php
	printf( /* translators: %1$s Review (linked to https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post) and %2$s Translating (linked to https://translate.wordpress.org/projects/wp-plugins/xml-sitemap-feed) */
		esc_html__( 'If you would like to contribute and share with the rest of the WordPress community, please consider writing a quick %1$s or help out with %2$s!', 'xml-sitemap-feed' ),
		'<a href="https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post" target="_blank">' . esc_html__( 'Review', 'xml-sitemap-feed' ) . '</a>',
		'<a href="https://translate.wordpress.org/projects/wp-plugins/xml-sitemap-feed" target="_blank">' . esc_html__( 'Translating', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
<p>
	<?php
	printf( /* translators: Github (linked to https://github.com/RavanH/xml-sitemap-feed) */
		esc_html__( 'For feature requests, reporting issues or contributing code, you can find and fork this plugin on %s.', 'xml-sitemap-feed' ),
		'<a href="https://github.com/RavanH/xml-sitemap-feed" target="_blank">' . esc_html__( 'GitHub', 'xml-sitemap-feed' ) . '</a>'
	);
	?>
</p>
