<?php
/**
 * BWT Oauth section stage 1
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><?php esc_html_e( 'Prerequisites', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %s: Link to Bing Webmaster Tools */
			esc_html__( 'You need an account on %s. In case you do not already have a Bing Webmaster account, sign up using any Microsoft, Google or Facebook ID.', 'xml-sitemap-feed' ),
			'<a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) . '</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: Link to https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b */
			esc_html__( 'Your site property needs to be set up in Bing Webmaster Tools. If you have not already done that, follow the instructions on %s.', 'xml-sitemap-feed' ),
			'<a href="https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b" target="_blank" rel="noopener noreferrer">https://www.bing.com/webmasters/help/add-and-verify-site-12184f8b</a>'
		);
		?>
	</li>
</ol>
<p>
	<?php esc_html_e( 'Follow the steps below to create a Bing Webmaster Tools API key.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Please use a Microsoft account that has at least Read-write access to the site property in Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?>
</p>
<h3><?php esc_html_e( 'Stage I. Create an API key', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s: Bing Webmaster Tools, %2$s: Settings */
			esc_html__( 'Sign in to your account on %1$s and open %2$s via the gear icon on the top right.', 'xml-sitemap-feed' ),
			'<strong><a href="https://www.bing.com/webmasters/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) . '</a></strong>',
			'<strong>' . esc_html__( 'Settings', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API Access, %2$s: API Key, %3$s: Generate API Key */
			esc_html__( 'Select %1$s (read and accept the Terms and Conditions if displayed) and go to %2$s. If you do not already have an API key, click %3$s to create one.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'API Access', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'API Key', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Generate API Key', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: Copy (button) */
			esc_html__( 'Click %s to copy the API key and paste it into the field below.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Copy', 'xml-sitemap-feed' ) . '</strong>',
		);
		?>
	</li>
</ol>
<hr>