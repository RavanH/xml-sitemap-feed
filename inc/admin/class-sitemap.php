<?php
/**
 * Admin for Sitemap
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * Admin Sitemap Class
 */
class Sitemap {
	/**
	 * Initialize hooks and filters.
	 */
	public static function init() {
		\add_action( 'admin_notices', array( '\XMLSF\Admin\Sitemap', 'check_advanced' ), 0 );

		// META.
		\add_action( 'add_meta_boxes', array( '\XMLSF\Admin\Sitemap', 'add_meta_box' ) );
		\add_action( 'save_post', array( '\XMLSF\Admin\Sitemap', 'save_metadata' ) );

		// Placeholders for advanced options.
		\add_action( 'xmlsf_posttype_archive_field_options', array( '\XMLSF\Admin\Fields', 'advanced_archive_field_options' ) );

		// QUICK EDIT.
		self::add_columns();
		\add_action( 'quick_edit_custom_box', array( '\XMLSF\Admin\Fields', 'quick_edit_fields' ) );
		\add_action( 'save_post', array( '\XMLSF\Admin\Sitemap', 'quick_edit_save' ) );
		\add_action( 'admin_head', array( '\XMLSF\Admin\Sitemap', 'quick_edit_script' ), 99 );
		// BULK EDIT.
		\add_action( 'bulk_edit_custom_box', array( '\XMLSF\Admin\Fields', 'bulk_edit_fields' ), 0 );
	}

	/**
	 * Plugin compatibility hooks and filters.
	 * Hooked on admin_init.
	 */
	public static function compat() {
		// Rank Math compatibility.
		if ( \is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Rank_Math', 'admin_notices' ) );
		}

		// Yoast SEO compatibility.
		if ( \is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\WP_SEO', 'admin_notices' ) );
		}

		// SEOPress compatibility.
		if ( \is_plugin_active( 'wp-seopress/seopress.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\SEOPress', 'admin_notices' ) );
		}

		// All in One SEO compatibility.
		if ( \is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\AIOSEO', 'admin_notices' ) );
		}

		// Google Sitemap Generator compatibility.
		if ( \is_plugin_active( 'google-sitemap-generator/sitemap.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\GS_Generator', 'admin_notices' ) );
		}

		// Slim SEO compatibility.
		if ( \is_plugin_active( 'slim-seo/slim-seo.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Slim_SEO', 'admin_notices' ) );
		}

		// Squirrly SEO compatibility.
		if ( \is_plugin_active( 'squirrly-seo/squirrly.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Squirrly_SEO', 'admin_notices' ) );
		}

		// Jetpack compatibility.
		if ( \is_plugin_active( 'jetpack/jetpack.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\Jetpack', 'admin_notices' ) );
		}

		// SEO Framework compatibility.
		if ( \is_plugin_active( 'autodescription/autodescription.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\SEO_Framework', 'admin_notices' ) );
		}

		// XML Sitemaps Manager compatibility.
		if ( \is_plugin_active( 'xml-sitemaps-manager/xml-sitemaps-manager.php' ) ) {
			\add_action( 'admin_notices', array( '\XMLSF\Compat\XMLSM', 'admin_notices' ) );
		}
	}

	/**
	 * Update actions for General Settings
	 */
	public static function update_server() {
		if ( ! xmlsf()->using_permalinks() ) {
			return;
		}

		// Set transients for flushing.
		set_transient( 'xmlsf_server_updated', true );
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
	 * Update actions for General Settings
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public static function update_disabled_providers( $old, $value ) {

		if ( $old === $value ) {
			return;
		}

		// When taxonomies have been disabled...
		if ( \in_array( 'taxonomies', (array) $value, true ) && ! \in_array( 'taxonomies', (array) $old, true ) ) {
			\delete_metadata( 'term', 0, 'term_modified', '', true );
		}

		// TODO Clear user meta cache if deactivating...
	}

	/**
	 * Update actions for Post Types setting
	 *
	 * @param mixed $old   Old option value.
	 * @param mixed $value Saved option value.
	 */
	public static function update_post_types( $old, $value ) {
		if ( $old === $value || ! is_array( $value ) ) {
			return;
		}

		$old            = (array) $old;
		$clear_images   = false;
		$clear_comments = false;

		foreach ( $value as $post_type => $settings ) {
			// Poll for changes that warrant clearing meta data.
			if ( isset( $old[ $post_type ] ) && \is_array( $old[ $post_type ] ) ) {

				if ( empty( $settings['active'] ) ) {
					if ( ! empty( $old[ $post_type ]['active'] ) ) {
						$clear_images   = true;
						$clear_comments = true;
					}
				} else {
					if ( isset( $old[ $post_type ]['tags'] ) && \is_array( $old[ $post_type ]['tags'] ) && isset( $old[ $post_type ]['tags']['image'] ) && $old[ $post_type ]['tags']['image'] !== $settings['tags']['image'] ) {
						$clear_images = true;
					}
					if ( ! empty( $old[ $post_type ]['update_lastmod_on_comments'] ) && empty( $settings['update_lastmod_on_comments'] ) ) {
						$clear_comments = true;
					}
				}
			}
		}

		// Clear images meta caches...
		if ( $clear_images ) {
			\delete_metadata( 'post', 0, '_xmlsf_image_attached', '', true );
			\delete_metadata( 'post', 0, '_xmlsf_image_featured', '', true );
			\set_transient( 'xmlsf_images_meta_primed', array() );
		}

		// Clear comments meta caches...
		if ( $clear_comments ) {
			\delete_metadata( 'post', 0, '_xmlsf_comment_date_gmt', '', true );
			\set_transient( 'xmlsf_comments_meta_primed', array() );
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
	 * Compare versions to known compatibility.
	 */
	public static function compatible_with_advanced() {
		// Return if plugin is not active.
		if ( ! is_plugin_active( 'xml-sitemap-feed-advanced/xml-sitemap-advanced.php' ) ) {
			return true;
		}

		// Check version.
		\defined( 'XMLSF_ADV_VERSION' ) || \define( 'XMLSF_ADV_VERSION', XMLSF_ADV_MIN_VERSION );

		return \version_compare( XMLSF_ADV_MIN_VERSION, XMLSF_ADV_VERSION, '<=' );
	}

	/**
	 * Check for conflicting plugins and their settings
	 */
	public static function check_advanced() {
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// XML Sitemap Advanced incompatibility notice.
		if ( ! self::compatible_with_advanced() && ! \in_array( 'xmlsf_advanced', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed' ), true ) ) {
			include XMLSF_DIR . '/views/admin/notice-xmlsf-advanced.php';
		}
	}

	/**
	 * META BOXES
	 */

	/**
	 * Adds a XML Sitemap box to the side column
	 */
	public static function add_meta_box() {
		$post_types = \xmlsf()->sitemap->get_post_types();
		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			// Only include metaboxes on post types that are included.
			\add_meta_box(
				'xmlsf_section',
				__( 'XML Sitemap', 'xml-sitemap-feed' ),
				array( __CLASS__, 'meta_box' ),
				$post_type,
				'side',
				'low'
			);
		}
	}

	/**
	 * Adds a XML Sitemap box to the side column
	 *
	 * @param WP_Post $post Post object.
	 */
	public static function meta_box( $post ) {
		// Use nonce for verification.
		\wp_nonce_field( XMLSF_BASENAME, '_xmlsf_nonce' );

		// Use get_post_meta to retrieve an existing value from the database and use the value for the form.
		$exclude  = \get_post_meta( $post->ID, '_xmlsf_exclude', true );
		$priority = \get_post_meta( $post->ID, '_xmlsf_priority', true );

		// value prechecks to prevent "invalid form control not focusable" when meta box is hidden.
		$priority = \is_numeric( $priority ) ? \XMLSF\sanitize_number( $priority ) : '';

		$description = sprintf(
			/* translators: Settings admin menu name, XML Sitemap admin page name */
			\esc_html__( 'Leave empty for automatic Priority as configured on %1$s > %2$s.', 'xml-sitemap-feed' ),
			\esc_html( \translate( 'Settings' ) ), // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
			'<a href="' . \admin_url( 'options-general.php' ) . '?page=xmlsf">' . \esc_html__( 'XML Sitemap', 'xml-sitemap-feed' ) . '</a>'
		);

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-meta-box.php';
	}

	/**
	 * When the post is saved, save our meta data
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save_metadata( $post_id ) {
		if (
			// verify nonce.
			! isset( $_POST['_xmlsf_nonce'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_xmlsf_nonce'] ), XMLSF_BASENAME ) ||
			// user not allowed.
			! \current_user_can( 'edit_post', $post_id )
		) {
			return;
		}

		// _xmlsf_priority
		if ( empty( $_POST['xmlsf_priority'] ) || ! \is_numeric( $_POST['xmlsf_priority'] ) ) {
			\delete_post_meta( $post_id, '_xmlsf_priority' );
		} else {
			\update_post_meta( $post_id, '_xmlsf_priority', \XMLSF\sanitize_number( \sanitize_text_field( \wp_unslash( $_POST['xmlsf_priority'] ) ) ) );
		}

		// _xmlsf_exclude
		if ( empty( $_POST['xmlsf_exclude'] ) ) {
			\delete_post_meta( $post_id, '_xmlsf_exclude' );
		} else {
			\update_post_meta( $post_id, '_xmlsf_exclude', \sanitize_key( $_POST['xmlsf_exclude'] ) );
		}
	}

	/**
	 * Add options page
	 */
	public static function add_options_page() {
		// This page will be under "Settings".
		$screen_id = \add_options_page(
			__( 'XML Sitemap', 'xml-sitemap-feed' ),
			__( 'XML Sitemap', 'xml-sitemap-feed' ),
			'manage_options',
			'xmlsf',
			array( __CLASS__, 'settings_page' )
		);

		// Settings hooks.
		\add_action( 'xmlsf_add_settings', array( __CLASS__, 'add_settings' ) );

		// Tools actions.
		\add_action( 'load-' . $screen_id, array( __CLASS__, 'tools_actions' ) );

		// Help tabs.
		\add_action( 'load-' . $screen_id, array( __CLASS__, 'help_tabs' ) );
	}

	/**
	 * Options page callback
	 */
	public static function settings_page() {
		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( \wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		\do_action( 'xmlsf_add_settings', $active_tab );

		// prepare sitemap link url.
		$sitemap_url = \xmlsf()->sitemap->get_sitemap_url();

		// Sidebar actions.
		\add_action(
			'xmlsf_admin_sidebar',
			function () {
				include XMLSF_DIR . '/views/admin/sidebar-links.php';
			},
			9
		);

		$disabled = \get_option( 'xmlsf_disabled_providers', \XMLSF\get_default_settings( 'disabled_providers' ) );

		// The actual settings page.
		include XMLSF_DIR . '/views/admin/page-sitemap.php';
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
				break;

			case 'general':
			default:
				/** GENERAL */
				// Sections.
				\add_settings_section(
					'general', // Section ID.
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
					'general'
				);
				\add_settings_field(
					'disabled_providers',
					\esc_html__( 'Disable sitemaps', 'xml-sitemap-feed' ),
					array( __NAMESPACE__ . '\Fields', 'disable_fields' ),
					'xmlsf_general',
					'general'
				);
		}
	}

	/**
	 * Register and add settings
	 */
	public static function register_settings() {
		// general.
		\register_setting(
			'xmlsf_general',
			'xmlsf_server',
			array( __NAMESPACE__ . '\Sanitize', 'server' )
		);
		\register_setting(
			'xmlsf_general',
			'xmlsf_disabled_providers',
			array( __NAMESPACE__ . '\Sanitize', 'disabled_providers' )
		);
		// post_types.
		\register_setting(
			'xmlsf_post_types',
			'xmlsf_post_types',
			array( __NAMESPACE__ . '\Sanitize', 'post_types' )
		);
		// post_type settings.
		\register_setting(
			'xmlsf_post_types',
			'xmlsf_post_type_settings',
			array( __NAMESPACE__ . '\Sanitize', 'post_type_settings' )
		);
		// taxonomies.
		\register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomy_settings',
			array( __NAMESPACE__ . '\Sanitize', 'taxonomy_settings' )
		);
		\register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomies',
			array( __NAMESPACE__ . '\Sanitize', 'taxonomies' )
		);
		// authors.
		\register_setting(
			'xmlsf_authors',
			'xmlsf_author_settings',
			array( __NAMESPACE__ . '\Sanitize', 'author_settings' )
		);
		\register_setting(
			'xmlsf_authors',
			'xmlsf_authors',
			array( __NAMESPACE__ . '\Sanitize', 'authors' )
		);
		// custom urls.
		\register_setting(
			'xmlsf_advanced',
			'xmlsf_urls',
			array( __NAMESPACE__ . '\Sanitize', 'custom_urls_settings' )
		);
		// custom sitemaps.
		\register_setting(
			'xmlsf_advanced',
			'xmlsf_custom_sitemaps',
			array( __NAMESPACE__ . '\Sanitize', 'custom_sitemaps_settings' )
		);

		// Settings ACTIONS & CHECKS.
		\add_action( 'update_option_xmlsf_server', array( __CLASS__, 'update_server' ) );
		\add_action( 'update_option_xmlsf_disabled_providers', array( __CLASS__, 'update_disabled_providers' ), 10, 2 );
		\add_action( 'update_option_xmlsf_post_types', array( __CLASS__, 'update_post_types' ), 10, 2 );

		// Check server updated.
		\add_action( 'load-settings_page_xmlsf', array( __CLASS__, 'maybe_server_updated' ) );
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

	/**
	 * Quick edit columns.
	 * Hooked on admin_init.
	 *
	 * @since 5.7
	 */
	public static function add_columns() {
		foreach ( \xmlsf()->sitemap->get_post_types() as $post_type ) {
			\add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'quick_edit_columns' ) );
			\add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'populate_columns' ) );
		}
	}

	/**
	 * Quick edit columns.
	 *
	 * @since 5.7
	 *
	 * @param string $column_array Column array.
	 */
	public static function quick_edit_columns( $column_array ) {
		$title = __( 'XML Sitemap', 'xml-sitemap-feed' );

		$column_array['xmlsf_exclude'] = '<span class="dashicons-before dashicons-networking" title="' . \esc_attr( $title ) . '"><span class="screen-reader-text">' . \esc_html( $title ) . '</span></span>';

		return $column_array;
	}

	/**
	 * Populate columns.
	 *
	 * @since 5.7
	 *
	 *  @param string $column_name Column name.
	 */
	public static function populate_columns( $column_name ) {
		global $post;
		if ( 'xmlsf_exclude' === $column_name ) {
			$exclude_meta = \get_post_meta( $post->ID, '_xmlsf_exclude', true );
			echo '<span class="_xmlsf_exclude" data-value="' . \esc_attr( $exclude_meta ) . '"></span>';
			if ( $exclude_meta ) {
				$title = \translate( 'No' );
				echo '<span class="dashicons-before dashicons-no" style="color:red" title="' . \esc_attr( $title ) . '"><span class="screen-reader-text">' . \esc_attr( $title ) . '</span></span>';
			} elseif ( 'publish' !== $post->post_status ) {
				$title = \translate( 'No' );
				echo '<span class="dashicons-before dashicons-no-alt" style="color:orange" title="' . \esc_attr( $title ) . '"><span class="screen-reader-text">' . \esc_attr( $title ) . '</span></span>';
			} else {
				$title = \translate( 'Yes' );
				echo '<span class="dashicons-before dashicons-yes" style="color:green" title="' . \esc_attr( $title ) . '"><span class="screen-reader-text">' . \esc_attr( $title ) . '</span></span>';
			}
		}
	}

	/**
	 * Quick edit save.
	 *
	 * @since 5.7
	 *
	 * @param int $post_id Post ID.
	 */
	public static function quick_edit_save( $post_id ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// check inline edit nonce.
		if ( empty( $_POST['_inline_edit'] ) || ! \wp_verify_nonce( \sanitize_key( $_POST['_inline_edit'] ), 'inlineeditnonce' ) ) {
			return;
		}

		// _xmlsf_exclude
		if ( empty( $_POST['xmlsf_exclude'] ) ) {
			\delete_post_meta( $post_id, '_xmlsf_exclude' );
		} else {
			\update_post_meta( $post_id, '_xmlsf_exclude', \sanitize_key( $_POST['xmlsf_exclude'] ) );
		}
	}

	/**
	 * Quick edit populate script.
	 * Hooked on admin_head.
	 *
	 * @since 5.7
	 */
	public static function quick_edit_script() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit' !== $screen->base ) {
			return;
		}
		?>

<style>th#xmlsf_exclude{width:20px}</style>
<script>
jQuery(document).ready(function ($) {
const wp_inline_edit = inlineEditPost.edit;
inlineEditPost.edit = function (post_id) {
	wp_inline_edit.apply(this, arguments);
	if (typeof (post_id) == 'object') {
		post_id = parseInt(this.getId(post_id));
	}
	if (post_id > 0) {
		const edit_row = $('#edit-' + post_id);
		const post_row = $('#post-' + post_id);

		const exclude = 1 == $('._xmlsf_exclude', post_row).data( "value" ) ? true : false;
		console.log( exclude );
		$(':input[name="xmlsf_exclude"]', edit_row).prop('checked', exclude);
	}
}; });
</script>
		<?php
	}
}
