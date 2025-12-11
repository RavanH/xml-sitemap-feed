<?php
/**
 * GSC Oauth section stage 1
 *
 * @package XML Sitemap & Google News
 */

?>
<h3><?php esc_html_e( 'Prerequisites', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %s: Link to Google Cloud Console */
			esc_html__( 'Your site property needs to be set up in Google Search Console. If you have not already done that, follow the instructions on %s.', 'xml-sitemap-feed' ),
			'<a href="https://support.google.com/webmasters/answer/34592" target="_blank" rel="noopener noreferrer">https://support.google.com/webmasters/answer/34592</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %s: Link to Google Cloud Console */
			esc_html__( 'You need a Google Cloud account. If you do not have one already, go to %s and create an account from there.', 'xml-sitemap-feed' ),
			'<a href="https://docs.cloud.google.com/docs/get-started" target="_blank" rel="noopener noreferrer">https://docs.cloud.google.com/docs/get-started</a>'
		);
		?>
	</li>
</ol>
<p><?php esc_html_e( 'Follow the steps below to create a Google Cloud Console project and obtain your credentials.', 'xml-sitemap-feed' ); ?> <?php esc_html_e( 'Please use a Google account that has at least Full access to the site property in Google Search Console.', 'xml-sitemap-feed' ); ?></p>
<h3><?php esc_html_e( 'Stage I. Create a Google Cloud Console project', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %s: Google Cloud Console (linked), %2$s create a new project (linked) */
			esc_html__( 'Go to the %1$s, log in and either %2$s or select an existing one.', 'xml-sitemap-feed' ),
			'<strong><a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Cloud Console', 'xml-sitemap-feed' ) . '</a></strong>',
			'<strong><a href="https://console.cloud.google.com/projectcreate" target="_blank" rel="noopener noreferrer">' . esc_html__( 'create a new project', 'xml-sitemap-feed' ) . '</a></strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: OAuth consent screen */
			esc_html__( 'If you created a new project, navigate to %1$s > %2$s.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'OAuth consent screen', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
		<ol>
			<li>
				<?php
				printf(
					/* translators: %1$s: Get started, %2$s: Create */
					esc_html__( 'Click the %1$s button, give your App a recognizable name, select a support e-mail address and hit %2$s.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Get started', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . esc_html__( 'Next', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %1$s: Internal, %2$s: External */
					esc_html__( 'At Audience choose %1$s if you have that option available (Workspace users). Otherwise choose %2$s.', 'xml-sitemap-feed' ),
					'<strong>' . esc_html__( 'Internal', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . esc_html__( 'External', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %1$s: External, %2$s: Audience, %3$s: Publish app */
					esc_html__( 'If you chose %1$s above, then navigate to %1$s and click %2$s.', 'xml-sitemap-feed' ),
					'<em>' . esc_html__( 'External', 'xml-sitemap-feed' ) . '</em>',
					'<strong>' . esc_html__( 'Audience', 'xml-sitemap-feed' ) . '</strong>',
					'<strong>' . esc_html__( 'Publish app', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</li>
		</ol>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: Library, %3$s Google Search Console API */
			esc_html__( 'Navigate to %1$s > %2$s. Search for %3$s and enable it for your project.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Library', 'xml-sitemap-feed' ) . '</strong>',
			'<strong><a href="https://console.cloud.google.com/apis/library/searchconsole.googleapis.com" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Search Console API', 'xml-sitemap-feed' ) . '</a></strong>'
		);
		?>
	</li>
</ol>

<h3><?php esc_html_e( 'Stage II. Create OAuth Credentials', 'xml-sitemap-feed' ); ?></h3>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s: API & Services, %2$s: Credentials, %3$s + Create credentials */
			esc_html__( 'Go to %1$s > %2$s and click %3$s.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Credentials', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( '+ Create credentials', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: OAuth client ID, %2$s: Web application */
			esc_html__( 'Select %1$s and choose %2$s as the Application type.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'OAuth client ID', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Web application', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Authorized redirect URIs, %2$s: The redirect URI to be registered in Google Cloud Console */
			esc_html__( 'In the %1$s field, add the following exact URI: %2$s', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Authorized redirect URIs', 'xml-sitemap-feed' ) . '</strong>',
			'<code>' . esc_url( site_url( 'index.php?' . self::$query_var ) ) . '</code>' // This is your plugin's custom endpoint URL.
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s: Create, %2$s: Client ID, %3$s: Client secret */
			esc_html__( 'Click %1$s button and a popup dialog will then display your %2$s and %3$s. Copy each of these and paste them into their respective fields below.', 'xml-sitemap-feed' ),
			'<strong>' . esc_html__( 'Create', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Client ID', 'xml-sitemap-feed' ) . '</strong>',
			'<strong>' . esc_html__( 'Client secret', 'xml-sitemap-feed' ) . '</strong>'
		);
		?>
	</li>
</ol>
<p><strong><?php esc_html_e( 'Important:', 'xml-sitemap-feed' ); ?></strong> <?php esc_html_e( 'Ensure the Redirect URI is copied and pasted exactly as shown.', 'xml-sitemap-feed' ); ?></p>
<p>
	<?php
	printf(
		/* translators: %s: Save Settings */
		esc_html__( 'After filling the fields below and clicking %1$s a button %2$s will allow you (or another site admin) to finalize the connection.', 'xml-sitemap-feed' ),
		'<strong>' . esc_html( \translate( 'Save Changes' ) ) . '</strong>', // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
		'<strong>' . esc_html__( 'Connect to Google Search Console', 'xml-sitemap-feed' ) . '</strong>'
	);
	?>
</p>
<hr>