<?php
/**
 * Admin for Sitemap
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

use XMLSF\GSC_Connect;

/**
 * Admin Sitemap Class
 */
class Sitemap_Settings {
	/**
	 * Prepare admin page load.
	 */
	public static function load() {
		// Run GSC actions.
		self::gsc_actions();

		// Run tools actions.
		self::tools_actions();

		// Prepare help tabs.
		self::help_tabs();

		// Maybe server updated.
		self::maybe_server_updated();

		// Settings hooks.
		\add_action( 'xmlsf_add_settings', array( __CLASS__, 'add_settings' ) );
	}

	/**
	 * Maybe server option was updated.
	 *
	 * Checks $_GET['settings-updated'] and transient 'xmlsf_server_updated'. Hooked into settings page load actions.
	 */
	public static function maybe_server_updated() {
		if ( ! empty( $_GET['settings-updated'] ) && \delete_transient( 'xmlsf_server_updated' ) ) {
			// Flush rewrite rules.
			\flush_rewrite_rules( false );

			// Check static file.
			$slug = \is_object( \xmlsf()->sitemap ) ? \xmlsf()->sitemap->slug() : 'sitemap';

			if ( \file_exists( \trailingslashit( \get_home_path() ) . $slug . '.xml' ) ) {
				\add_settings_error(
					'static_files_notice',
					'static_file_' . $slug,
					\sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
						\esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
						\esc_html( $slug . '.xml' ),
						'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
					),
					'warning'
				);
			}
		}
	}

	/**
	 * Clear settings
	 */
	public static function clear_settings() {
		$defaults = \XMLSF\get_default_settings();

		unset( $defaults['sitemaps'] );

		foreach ( $defaults as $option => $settings ) {
			\update_option( 'xmlsf_' . $option, $settings );
		}
	}

	/**
	 * Run admin actions.
	 */
	public static function gsc_actions() {
		// Skip if doing ajax or no valid nonce.
		if ( \wp_doing_ajax() || ! isset( $_POST['_xmlsf_gsc_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_gsc_nonce'] ), XMLSF_BASENAME . '-gsc' ) ) {
			return;
		}

		// Handle disconnection if requested. Runs before anything else.
		if ( isset( $_POST['xmlsf_gsc_disconnect'] ) ) {
			// Clear the refresh token and any related options.
			GSC_Connect::disconnect();

			\add_settings_error(
				'xmlsf_gsc_connect',
				'gsc_disconnected',
				__( 'Disconnected from Google Search Console successfully.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		// Handle manual submit.
		if ( isset( $_POST['xmlsf_gsc_manual_submit'] ) ) {
			// Skip submission if within the grace period for Google News sitemap.
			if ( \get_transient( 'sitemap_notifier_submission' ) ) {
				$timeframe = (int) \apply_filters( 'xmlsf_gsc_manual_submit_timeframe', 360 );
				$message   = \sprintf( /* translators: %1$s: Google News Sitemap, %2$d: number of seconds */ esc_html__( 'Your %1$s submission was skipped: Already sent within the last %2$d seconds.', 'xml-sitemap-feed' ), esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ), $timeframe );

				\do_action( 'sitemap_notifier_manual_submission', $message, 'warning' );

				\add_settings_error(
					'xmlsf_gsc_connect',
					'gsc_manual_submit',
					$message,
					'error'
				);
			} else {
				$sitemap = xmlsf()->sitemap->get_sitemap_url();
				$result  = GSC_Connect::submit( $sitemap );
				if ( \is_wp_error( $result ) ) {
					$message = \sprintf( /* translators: %1$s: Google News Sitemap, %2$s: Error message */ esc_html__( 'Your %1$s submission failed: %2$s', 'xml-sitemap-feed' ), esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ), $result->get_error_message() );

					\do_action( 'sitemap_notifier_manual_submission', $message, 'error' );

					\add_settings_error(
						'xmlsf_gsc_connect',
						'gsc_manual_submit',
						$message,
						'error'
					);
				} else {
					$message = \sprintf( /* translators: %s: Google News Sitemap */ esc_html__( 'Your %s was submitted successfully.', 'xml-sitemap-feed' ), esc_html__( 'XML Sitemap Index', 'xml-sitemap-feed' ) );

					\do_action( 'sitemap_notifier_manual_submission', $message, 'success' );

					\add_settings_error(
						'xmlsf_gsc_connect',
						'gsc_manual_submit',
						$message,
						'success'
					);

					$timeframe = \apply_filters( 'xmlsf_gsc_manual_submit_timeframe', 360 );
					\set_transient( 'sitemap_notifier_submission', true, $timeframe );
				}
			}
		}
	}

	/**
	 * Tools actions
	 */
	public static function tools_actions() {
		if ( ! isset( $_POST['_xmlsf_help_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_help_nonce'] ), XMLSF_BASENAME . '-help' ) ) {
			return;
		}

		if ( isset( $_POST['xmlsf-flush-rewrite-rules'] ) ) {
			// Flush rewrite rules.
			\flush_rewrite_rules( false );
			\add_settings_error(
				'flush_admin_notice',
				'flush_admin_notice',
				__( 'WordPress rewrite rules have been flushed.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		if ( isset( $_POST['xmlsf-check-conflicts'] ) ) {
			// Reset ignored warnings.
			\delete_user_meta( \get_current_user_id(), 'xmlsf_dismissed' );

			// Check static file.
			$slug = \is_object( \xmlsf()->sitemap ) ? \xmlsf()->sitemap->slug() : 'sitemap';

			if ( \file_exists( \trailingslashit( \get_home_path() ) . $slug . '.xml' ) ) {
				\add_settings_error(
					'static_files_notice',
					'static_file_' . $slug,
					\sprintf( /* translators: %1$s file name, %2$s is XML Sitemap (linked to options-reading.php) */
						\esc_html__( 'A conflicting static file has been found: %1$s. Either delete it or disable the corresponding %2$s.', 'xml-sitemap-feed' ),
						\esc_html( $slug . '.xml' ),
						'<a href="' . \esc_url( \admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
					),
					'warning'
				);
			} else {
				\add_settings_error(
					'static_files_notice',
					'static_files',
					\esc_html__( 'No conflicting static files found.', 'xml-sitemap-feed' ),
					'success'
				);
			}
		}

		if ( isset( $_POST['xmlsf-clear-settings'] ) ) {
			self::clear_settings();
			\add_settings_error(
				'notice_clear_settings',
				'notice_clear_settings',
				\esc_html__( 'Settings reset to the plugin defaults.', 'xml-sitemap-feed' ),
				'updated'
			);
		}

		if ( isset( $_POST['xmlsf-clear-term-meta'] ) ) {
			// Remove terms metadata.
			\delete_metadata( 'term', 0, 'term_modified', '', true );

			\add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				\esc_html__( 'Sitemap term meta cache has been cleared.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		if ( isset( $_POST['xmlsf-clear-user-meta'] ) ) {
			// Remove terms metadata.
			\delete_metadata( 'user', 0, 'user_modified', '', true );

			\add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				\esc_html__( 'Sitemap author meta cache has been cleared.', 'xml-sitemap-feed' ),
				'success'
			);
		}

		if ( isset( $_POST['xmlsf-clear-post-meta'] ) ) {
			// Remove metadata.
			\delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
			\delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
			\set_transient( 'xmlsf_images_meta_primed', array() );

			\delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
			\set_transient( 'xmlsf_comments_meta_primed', array() );

			\add_settings_error(
				'clear_meta_notice',
				'clear_meta_notice',
				\esc_html__( 'Sitemap post meta caches have been cleared.', 'xml-sitemap-feed' ),
				'updated'
			);
		}
	}

	/**
	 * Options page callback
	 */
	public static function settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\do_action( 'xmlsf_add_settings', $active_tab );

		// prepare sitemap link url.
		$sitemap_url = \xmlsf()->sitemap->get_sitemap_url();

		// Sidebar actions.
		\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_gsc_connect' ), 5 );
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-tools.php';
			},
			9
		);
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-links.php';
			},
			9
		);
		// Advanced plugin plug.
		if ( ! \is_plugin_active( 'xml-sitemap-feed-advanced/xml-sitemap-advanced.php' ) ) {
			\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_adv_plug' ), 6 );
			\add_action( 'xmlsf_admin_sidebar', array( __CLASS__, 'admin_sidebar_priority_support' ), 11 );
		}

		$disabled = \get_option( 'xmlsf_disabled_providers', \XMLSF\get_default_settings( 'disabled_providers' ) );

		// The actual settings page.
		include XMLSF_DIR . '/views/admin/page-sitemap.php';
	}

	/**
	 * Admin sidbar GSC section
	 */
	public static function admin_sidebar_gsc_connect() {
		$sitemap_desc      = __( 'XML Sitemap Index', 'xml-sitemap-feed' );
		$settings_page_url = add_query_arg( 'ref', 'xmlsf', GSC_Connect::get_settings_url() );

		include XMLSF_DIR . '/views/admin/sidebar-gsc-connect.php';
	}

	/**
	 * Admin sidebar Priority Support section
	 */
	public static function admin_sidebar_priority_support() {
		$adv_plugin_name = __( 'XML Sitemap Advanced', 'xml-sitemap-feed' );
		$adv_plugin_url  = 'https://premium.status301.com/downloads/xml-sitemap-advanced/';

		include XMLSF_DIR . '/views/admin/sidebar-priority-support.php';
	}

	/**
	 * Admin sidebar Priority Support section
	 */
	public static function admin_sidebar_adv_plug() {
		$adv_plugin_name = __( 'XML Sitemap Advanced', 'xml-sitemap-feed' );
		$adv_plugin_url  = 'https://premium.status301.com/downloads/xml-sitemap-advanced/';
		$sitemap_name    = __( 'XML Sitemap Index', 'xml-sitemap-feed' );

		include XMLSF_DIR . '/views/admin/sidebar-advanced-plug.php';
	}

	/**
	 * Add settings sections and fields.
	 *
	 * @param string $active_tab The active tab slug.
	 */
	public static function add_settings( $active_tab = '' ) {
		switch ( $active_tab ) {
			case 'post_types':
				/** POST TYPES */
				\add_settings_section(
					'xml_sitemap_post_types_section',
					'',
					'',
					'xmlsf_post_types'
				);

				\add_settings_field(
					'xmlsf_sitemap_post_types_limit',
					\translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
					array( __NAMESPACE__ . '\Fields', 'post_types_general_fields' ),
					'xmlsf_post_types',
					'xml_sitemap_post_types_section'
				);

				$post_types = \get_post_types( array( 'public' => true ) );
				// Make sure post types are allowed and publicly viewable.
				$post_types = \array_diff( $post_types, \xmlsf()->disabled_post_types() );
				$post_types = \array_filter( $post_types, 'is_post_type_viewable' );

				if ( \is_array( $post_types ) && ! empty( $post_types ) ) {
					foreach ( $post_types as $post_type ) {
						$obj = \get_post_type_object( $post_type );
						if ( ! \is_object( $obj ) ) {
							continue;
						}
						\add_settings_field(
							'xmlsf_post_type_' . $obj->name,
							$obj->label,
							array( __NAMESPACE__ . '\Fields', 'post_type_fields' ),
							'xmlsf_post_types',
							'xml_sitemap_post_types_section',
							$post_type
						);
						// Note: (ab)using section name parameter to pass post type name.
					}
				}
				break;

			case 'taxonomies':
				/** TAXONOMIES */
				\add_settings_section(
					'xml_sitemap_taxonomies_section',
					'',
					'',
					'xmlsf_taxonomies'
				);
				\add_settings_field(
					'xmlsf_taxonomy_settings',
					translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
					array( __NAMESPACE__ . '\Fields', 'taxonomy_settings_field' ),
					'xmlsf_taxonomies',
					'xml_sitemap_taxonomies_section'
				);
				\add_settings_field(
					'xmlsf_taxonomies',
					__( 'Taxonomies', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'taxonomies_field' ),
					'xmlsf_taxonomies',
					'xml_sitemap_taxonomies_section'
				);
				break;

			case 'authors':
				/** AUTHORS */
				\add_settings_section(
					'xml_sitemap_authors_section',
					'',
					'',
					'xmlsf_authors'
				);
				\add_settings_field(
					'xmlsf_author_settings',
					translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
					array( __NAMESPACE__ . '\Fields', 'author_settings_field' ),
					'xmlsf_authors',
					'xml_sitemap_authors_section'
				);
				\add_settings_field(
					'xmlsf_authors',
					__( 'Authors', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'authors_field' ),
					'xmlsf_authors',
					'xml_sitemap_authors_section'
				);
				break;

			case 'advanced':
				/** ADVANCED */
				\add_settings_section(
					'xml_sitemap_advanced_section',
					'',
					'',
					'xmlsf_advanced'
				);
				// custom name.
				\add_settings_field(
					'xmlsf_sitemap_name',
					'<label for="xmlsf_sitemap_name">' . __( 'XML Sitemap URL', 'xml-sitemap-feed' ) . '</label>',
					array( __NAMESPACE__ . '\Fields', 'xmlsf_sitemap_slug_field' ),
					'xmlsf_advanced',
					'xml_sitemap_advanced_section'
				);
				// custom urls.
				\add_settings_field(
					'xmlsf_urls',
					__( 'External web pages', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'urls_settings_field' ),
					'xmlsf_advanced',
					'xml_sitemap_advanced_section'
				);
				// custom sitemaps.
				\add_settings_field(
					'xmlsf_custom_sitemaps',
					__( 'External XML Sitemaps', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'custom_sitemaps_settings_field' ),
					'xmlsf_advanced',
					'xml_sitemap_advanced_section'
				);
				// Sitemap notifier.
				\add_settings_field(
					'xmlsf_sitemap_notifier',
					__( 'Sitemap notifier', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'sitemap_notifier_field' ),
					'xmlsf_advanced',
					'xml_sitemap_advanced_section'
				);
				break;

			case 'general':
			default:
				/** GENERAL */
				// Sections.
				\add_settings_section(
					'xml_sitemap_general_section', // Section ID.
					'', // Title.
					'', // Intro callback.
					'xmlsf_general' // Page slug.
				);
				// Fields.
				\add_settings_field(
					'server',
					\translate( 'Server' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
					array( __NAMESPACE__ . '\Fields', 'server_field' ),
					'xmlsf_general',
					'xml_sitemap_general_section'
				);
				\add_settings_field(
					'disabled_providers',
					\esc_html__( 'Disable sitemaps', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'disable_fields' ),
					'xmlsf_general',
					'xml_sitemap_general_section'
				);

				// GSC Sitemap data.
				\add_settings_section(
					'xml_sitemap_gsc_data_section',
					__( 'Google Search Console Report', 'xml-sitemap-feed' ),
					function () {
						include XMLSF_DIR . '/views/admin/section-gsc-data.php';
					},
					'xmlsf_general'
				);
		}
	}

	/**
	 * Help tabs
	 */
	public static function help_tabs() {
		$screen     = \get_current_screen();
		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( $_GET['tab'] ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sitemaps.php';
		include XMLSF_DIR . '/views/admin/help-tab-support.php';
		$content = \ob_get_clean();

		$screen->add_help_tab(
			array(
				'id'      => 'sitemap-settings',
				'title'   => __( 'XML Sitemap', 'xml-sitemap-feed' ),
				'content' => $content,
			)
		);

		switch ( $active_tab ) {
			case 'general':
				// Server.
				$content  = '<p>' . \esc_html__( 'Select your XML Sitemap generator here.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p><strong>' . \esc_html( \translate( 'WordPress' ) ) . '</strong></p>'; // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
				$content .= '<p>' . \esc_html__( 'The default sitemap server is light-weight, effective and compatible with most installations. But it is also limited. The XML Sitemaps & Google News plugin adds some essential features and options to the default sitemap generator but if these are not enough, try the plugin sitemap server.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p><strong>' . \esc_html( \translate( 'Plugin' ) ) . '</strong></p>'; // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
				$content .= '<p>' . \esc_html__( 'The plugin sitemap server generates the sitemap in a different way, allowing some additional features and configuration options. However, it is not guaranteed to be compatible with your specific WordPress installation.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-general-server',
						'title'   => \translate( 'Server' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				// Disable.
				$content  = '<p>' . \esc_html__( 'By default, all public content types, taxonomy archives and author archives are included in the XML Sitemap index. If you wish to exclude any content or archive types, you can disable them here.', 'xml-sitemap-feed' ) . '</p>';
				$content .= '<p>' . sprintf( /* translators: %1$s Taxonomies, %2$s Taxonomies linked to the respective tab */
					\esc_html__( 'Select %1$s here to exclude then all taxonomy archives from the sitemap index. To exclude only a particular taxonomy, please go to the %2$s tab.', 'xml-sitemap-feed' ),
					'<strong>' . \esc_html__( 'Taxonomies', 'xml-sitemap-feed' ) . '</strong>',
					'<a href="?page=xmlsf&tab=taxonomies">' . \esc_html__( 'Taxonomies', 'xml-sitemap-feed' ) . '</a>'
				) . '</p>';
				$content .= '<p>' . sprintf( /* translators: %1$s Authors, %2$s Authors linked to the respective tab  */
					\esc_html__( 'Select %1$s here to exclude all author archives from the sitemap index. To exclude only a particular author or user group, please go to the %2$s tab.', 'xml-sitemap-feed' ),
					'<strong>' . \esc_html__( 'Authors', 'xml-sitemap-feed' ) . '</strong>',
					'<a href="?page=xmlsf&tab=authors">' . \esc_html__( 'Authors', 'xml-sitemap-feed' ) . '</a>'
				) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-general-disable',
						'title'   => \esc_html__( 'Disable sitemaps', 'xml-sitemap-feed' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				break;

			case 'post_types':
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-post-types-general.php';
				$content = \ob_get_clean();
				// General Settings.
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-post-type-general',
						'title'   => \translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);

				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-post-types.php';
				$content = \ob_get_clean();
				// Post type options.
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-post-types',
						'title'   => __( 'Post types', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'taxonomies':
				// Settings.
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-taxonomies.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-taxonomies-general',
						'title'   => \translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				// Taxonomies.
				$content  = '<p><strong>' . \esc_html__( 'Include these taxonomies', 'xml-sitemap-feed' ) . '</strong></p>';
				$content .= '<p>' . \esc_html__( 'Select the taxonomies to include in the sitemap index. Select none to automatically include all public taxonomies.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-taxonomies',
						'title'   => __( 'Taxonomies', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'authors':
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-authors.php';
				$content = \ob_get_clean();
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-authors-general',
						'title'   => \translate( 'General' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				// Authors.
				$content  = '<p><strong>' . \esc_html__( 'Include these authors', 'xml-sitemap-feed' ) . '</strong></p>';
				$content .= '<p>' . \esc_html__( 'Select the authors to include in the author sitemap. Select none to automatically include all authors.', 'xml-sitemap-feed' ) . '</p>';
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-authors',
						'title'   => __( 'Authors', 'xml-sitemap-feed' ),
						'content' => $content,
					)
				);
				break;

			case 'advanced':
				\ob_start();
				include XMLSF_DIR . '/views/admin/help-tab-advanced.php';
				$content = \ob_get_clean();
				// Add help tab.
				$screen->add_help_tab(
					array(
						'id'      => 'sitemap-settings-advanced',
						'title'   => \translate( 'Advanced' ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
						'content' => $content,
					)
				);
				break;
		}

		// Hook for additional/advanced help tab.
		\do_action( 'xmlsf_sitemap_help_tabs', $screen, $active_tab );

		\ob_start();
		include XMLSF_DIR . '/views/admin/help-tab-sidebar.php';
		$content = \ob_get_clean();

		$screen->set_help_sidebar( $content );
	}
}
