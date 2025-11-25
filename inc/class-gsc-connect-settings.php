<?php
/**
 * Status301 Premium Google Search Console Connection Onboarding
 *
 * @package XML Sitemap & Google News - Google News Advanced
 */

namespace XMLSF;

/**
 * Helper class with public methods to set up a Google Search Console connection.
 *
 * @author RavanH
 * @version 1.0
 */
class GSC_Connect_Settings extends GSC_Connect {

	/**
	 * Placeholder for the saved password.
	 * This is used to avoid showing the actual password in the settings page.
	 * If this value is present, it means the password is saved and should not be changed.
	 *
	 * @var string
	 */
	public static $pw_placeholder = 'DONT_BOTHER_CLIENT_SECRET_ENCRYPTED';

	/**
	 * Display the plugin options page HTML.
	 */
	public static function options_page_render() {
		// Check user capabilities.
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php \esc_html_e( 'Google Search Console Connection', 'xml-sitemap-feed' ); ?></h1>

			<form action="options.php" method="post">
				<?php
				// Output security fields for the registered setting section.
				\settings_fields( self::$option_group );

				// Output setting sections and fields.
				\do_settings_sections( self::$page_slug );

				// Output save settings button.
				\submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Callback function for the Google Search Console OAuth Settings section header.
	 */
	public static function oauth_section_callback() {
		// Prepare the option if it does not already exist. Sets it as non-autoloaded option.
		add_option( self::$option_group, '', '', false );

		// Get existing data from DB.
		$options = (array) \get_option( self::$option_group, array() );
		?>
		<p>
			<?php \esc_html_e( 'To allow sitemap submission, a connection between your website and Google Search Console needs to be created. This will be set up in two stages: (1) Creating a Google Cloud Console project with OAuth credentials and (2) Authorizing the connection.', 'xml-sitemap-feed' ); ?>
			<?php
				\printf(
					/* translators: %s: Link to detailed documentation */
					\esc_html__( 'For more detailed instructions, please refer to our %s.', 'xml-sitemap-feed' ),
					'<a href="https://premium.status301.com/knowledge-base/xml-sitemap-google-news/automatically-notify-google-on-news-sitemap-updates/" target="_blank" rel="noopener noreferrer">' . \esc_html__( 'Knowledge Base', 'xml-sitemap-feed' ) . '</a>'
				);
			?>
		</p>
		<?php
		// Check if the Google Client ID and Secret are set.
		if ( empty( $options['google_client_id'] ) || empty( $options['google_client_secret'] ) ) :
			?>
			<h3><?php \esc_html_e( 'Stage 1/2. Create a Google Cloud Console project', 'xml-sitemap-feed' ); ?></h3>
			<p><?php \esc_html_e( 'Follow the steps below to create a Google Cloud Console project and obtain your credentials.', 'xml-sitemap-feed' ); ?> <?php \esc_html_e( 'Please use a Google account that has Full access to the site property in Google Search Console.', 'xml-sitemap-feed' ); ?></p>
			<ol>
				<li>
					<?php
					\printf(
						/* translators: %s: Link to Google Cloud Console */
						\esc_html__( 'Go to the %s and either create a new project or select an existing one.', 'xml-sitemap-feed' ),
						'<strong><a href="https://console.cloud.google.com/" target="_blank" rel="noopener noreferrer">' . \esc_html__( 'Google Cloud Console', 'xml-sitemap-feed' ) . '</a></strong>'
					);
					?>
				</li>
				<li>
					<?php
					\printf(
						/* translators: %1$s: API & Services, %2$s: OAuth consent screen */
						\esc_html__( 'If you created a new project, navigate to %1$s > %2$s.', 'xml-sitemap-feed' ),
						'<strong>' . \esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html__( 'OAuth consent screen', 'xml-sitemap-feed' ) . '</strong>'
					);
					?>
					<ul>
						<li>
							<?php
							\printf(
								/* translators: %1$s: Get started, %2$s: Create */
								\esc_html__( 'Click the %1$s button, give your App a name and follow the steps to finally reach %2$s.', 'xml-sitemap-feed' ),
								'<strong>' . \esc_html__( 'Get started', 'xml-sitemap-feed' ) . '</strong>',
								'<strong>' . \esc_html__( 'Create', 'xml-sitemap-feed' ) . '</strong>'
							);
							?>
						</li>
						<li>
							<?php
							\printf(
								/* translators: %1$s: Audience, %2$s: Publish app */
								\esc_html__( 'Then navigate to %1$s and, if available, click %2$s.', 'xml-sitemap-feed' ),
								'<strong>' . \esc_html__( 'Audience', 'xml-sitemap-feed' ) . '</strong>',
								'<strong>' . \esc_html__( 'Publish app', 'xml-sitemap-feed' ) . '</strong>'
							);
							?>
						</li>
					</ul>
				</li>
				<li>
					<?php
					\printf(
						/* translators: %1$s: API & Services, %2$s: Library, %3$s Google Search Console API */
						\esc_html__( 'Navigate to %1$s > %2$s. Search for %3$s and enable it for your project.', 'xml-sitemap-feed' ),
						'<strong>' . \esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html__( 'Library', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html__( 'Google Search Console API', 'xml-sitemap-feed' ) . '</strong>'
					);
					?>
				</li>
				<li>
					<?php
					\printf(
						/* translators: %1$s: API & Services, %2$s: Credentials, %3$s + Create credentials */
						\esc_html__( 'Go to %1$s > %2$s and click %3$s.', 'xml-sitemap-feed' ),
						'<strong>' . \esc_html__( 'APIs & Services', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html__( 'Credentials', 'xml-sitemap-feed' ) . '</strong>',
						'<strong>' . \esc_html__( '+ Create credentials', 'xml-sitemap-feed' ) . '</strong>'
					);
					?>
					<ul>
						<li>
							<?php
							\printf(
								/* translators: %1$s: OAuth client ID, %2$s: Web application */
								\esc_html__( 'Select %1$s and choose %2$s as the Application type.', 'xml-sitemap-feed' ),
								'<strong>' . \esc_html__( 'OAuth client ID', 'xml-sitemap-feed' ) . '</strong>',
								'<strong>' . \esc_html__( 'Web application', 'xml-sitemap-feed' ) . '</strong>'
							);
							?>
						</li>
						<li>
							<?php
							\printf(
								/* translators: %1$s: Authorized redirect URIs, %2$s: The redirect URI to be registered in Google Cloud Console */
								\esc_html__( 'In the %1$s field, add the following exact URI: %2$s', 'xml-sitemap-feed' ),
								'<strong>' . \esc_html__( 'Authorized redirect URIs', 'xml-sitemap-feed' ) . '</strong>',
								'<code>' . \esc_url( \site_url( 'index.php?' . self::$query_var ) ) . '</code>' // This is your plugin's custom endpoint URL.
							);
							?>
						</li>
						<li>
							<?php
							\printf(
								/* translators: %1$s: Create, %2$s: Client ID, %3$s: Client secret */
								\esc_html__( 'Click %1$s button and a popup dialog will then display your %2$s and %3$s. Copy each of these and paste them into their respective fields below.', 'xml-sitemap-feed' ),
								'<strong>' . \esc_html__( 'Create', 'xml-sitemap-feed' ) . '</strong>',
								'<strong>' . \esc_html__( 'Client ID', 'xml-sitemap-feed' ) . '</strong>',
								'<strong>' . \esc_html__( 'Client secret', 'xml-sitemap-feed' ) . '</strong>'
							);
							?>
						</li>
					</ul>
				</li>
			</ol>
			<p><strong><?php \esc_html_e( 'Important:', 'xml-sitemap-feed' ); ?></strong> <?php \esc_html_e( 'Ensure the Redirect URI is copied and pasted exactly as shown.', 'xml-sitemap-feed' ); ?></p>
			<p>
				<?php
				\printf(
					/* translators: %s: Save Settings */
					esc_html__( 'After filling the fields below and clicking %1$s a button %2$s will allow you to finalize the connection.', 'xml-sitemap-feed' ),
					'<strong>' . \esc_html( \translate( 'Save Changes' ) ) . '</strong>', // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
					'<strong>' . \esc_html__( 'Connect to Google Search Console', 'xml-sitemap-feed' ) . '</strong>'
				);
				?>
			</p>
			<hr>
			<?php
		else :
			$redirect_uri = \site_url( 'index.php?' . self::$query_var );
			$oauth_url    = \add_query_arg(
				array(
					'client_id'     => $options['google_client_id'],
					'redirect_uri'  => rawurlencode( $redirect_uri ),
					'scope'         => rawurlencode( 'https://www.googleapis.com/auth/webmasters' ),
					'response_type' => 'code',
					'access_type'   => 'offline', // Request a refresh token.
					'prompt'        => 'consent', // Ensure consent screen is shown.
				),
				'https://accounts.google.com/o/oauth2/auth'
			);
			?>
			<h3><?php \esc_html_e( 'Stage 2/2. Authorize the connection', 'xml-sitemap-feed' ); ?></h3>
			<a href="<?php echo \esc_url( $oauth_url ); ?>" class="button button-primary">
				<?php \esc_html_e( 'Connect to Google Search Console', 'xml-sitemap-feed' ); ?>
			</a>
			<p class="description">
				<?php \esc_html_e( 'You will be redirected to Google to authorize your site.', 'xml-sitemap-feed' ); ?> <?php \esc_html_e( 'Please use a Google account that has Full access to the site property in Google Search Console.', 'xml-sitemap-feed' ); ?>
				<br>
				<?php
				\printf(
					/* translators: %s the URL */
					\esc_html__( 'The redirect URI for your Google Cloud Console OAuth 2.0 client configuration should be: %s', 'xml-sitemap-feed' ),
					'<code>' . \esc_url( \site_url( 'index.php?' . self::$query_var ) ) . '</code>'
				);
				?>
			</p>
			<p><?php \esc_html_e( 'After successful authorization, you will be able to configure the plugin settings and start submitting your sitemap to Google Search Console manually or automatically on content updates.', 'xml-sitemap-feed' ); ?></p>
			<hr>
			<p><?php \printf( /* translators: %1$s Client ID, %2$s Save Changes */ \esc_html__( 'To restart the setup process, clear the %1$s field and %2$s.', 'xml-sitemap-feed' ), \esc_html__( 'Client ID', 'xml-sitemap-feed' ), \esc_html( \translate( 'Save Changes' ) ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?>
			</p>
			<?php
		endif;
	}

	/**
	 * Render the text field for the Google Client ID.
	 */
	public static function google_client_id_render() {
		$options   = (array) \get_option( self::$option_group, array() );
		$client_id = isset( $options['google_client_id'] ) ? \sanitize_text_field( $options['google_client_id'] ) : '';
		?>
		<input type="text" autocomplete="off" name="<?php echo \esc_attr( self::$option_group ); ?>[google_client_id]" id="xmlsf_notifier_google_client_id" value="<?php echo \esc_attr( $client_id ); ?>" class="regular-text">
		<p class="description">
			<?php \esc_html_e( 'Enter your Google Cloud Project Client ID. You can find this in the Google Cloud Console under APIs & Services > Credentials.', 'xml-sitemap-feed' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the text field for the Google Client Secret.
	 */
	public static function google_client_secret_render() {
		$options       = (array) \get_option( self::$option_group, array() );
		$client_secret = ! empty( $options['google_client_secret'] ) ? self::$pw_placeholder : '';
		?>
		<input type="password" autocomplete="new-password" name="<?php echo \esc_attr( self::$option_group ); ?>[google_client_secret]" id="xmlsf_notifier_google_client_secret" value="<?php echo \esc_attr( $client_secret ); ?>" class="regular-text">
		<p class="description">
			<?php \esc_html_e( 'Enter your Google Cloud Project Client Secret. Keep this secret confidential. If you loose it, you will need to create a new one in the Google Cloud Console under APIs & Services > Credentials.', 'xml-sitemap-feed' ); ?>
		</p>
		<?php
	}

	/**
	 * Sanitize the plugin options before saving.
	 *
	 * @param array $input The options array submitted by the form.
	 *
	 * @return array The sanitized options array.
	 */
	public static function sanitize_settings( $input ) {
		$sanitized = array();
		$options   = (array) \get_option( self::$option_group, array() ); // Not strictly needed if only sanitizing submitted input.

		// Sanitize Google Client ID.
		if ( isset( $input['google_client_id'] ) ) {
			$sanitized['google_client_id'] = \sanitize_text_field( $input['google_client_id'] );
		} else {
			$sanitized['google_client_id'] = isset( $options['google_client_id'] ) ? $options['google_client_id'] : '';
		}

		// Sanitize Google Client Secret.
		if ( isset( $input['google_client_secret'] ) && self::$pw_placeholder !== $input['google_client_secret'] ) {
			$sanitized['google_client_secret'] = ! empty( $input['google_client_secret'] ) ? GSC_Oauth_Handler::encrypt( \sanitize_text_field( $input['google_client_secret'] ) ) : '';
		} else {
			$sanitized['google_client_secret'] = isset( $options['google_client_secret'] ) ? $options['google_client_secret'] : '';
		}

		// Make sure to not loose existing refresh token, but only if client id is set and was not changed.
		if ( ! empty( $options['google_refresh_token'] ) && $sanitized['google_client_id'] === $options['google_client_id'] ) {
			$sanitized['google_refresh_token'] = $options['google_refresh_token'];
		}

		return $sanitized;
	}
}
