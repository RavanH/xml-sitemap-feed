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
		if ( ! self::compatible_with_advanced() && ! \in_array( 'xmlsf_advanced', (array) \get_user_meta( \get_current_user_id(), 'xmlsf_dismissed', false ), true ) ) {
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
		$post_id = $post->ID;

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
			array( __NAMESPACE__ . '\Sitemap_Settings', 'settings_page' )
		);

		// Load settings.
		\add_action( 'load-' . $screen_id, array( __NAMESPACE__ . '\Sitemap_Settings', 'load' ) );
	}

	/**
	 * Register and add settings
	 */
	public static function register_settings() {
		// general.
		\register_setting(
			'xmlsf_general',
			'xmlsf_server',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'server' ) )
		);
		\register_setting(
			'xmlsf_general',
			'xmlsf_disabled_providers',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'disabled_providers' ) )
		);
		// post_types.
		\register_setting(
			'xmlsf_post_types',
			'xmlsf_post_types',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'post_types' ) )
		);
		// post_type settings.
		\register_setting(
			'xmlsf_post_types',
			'xmlsf_post_type_settings',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'post_type_settings' ) )
		);
		// taxonomies.
		\register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomy_settings',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'taxonomy_settings' ) )
		);
		\register_setting(
			'xmlsf_taxonomies',
			'xmlsf_taxonomies',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'taxonomies' ) )
		);
		// authors.
		\register_setting(
			'xmlsf_authors',
			'xmlsf_author_settings',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'author_settings' ) )
		);
		\register_setting(
			'xmlsf_authors',
			'xmlsf_authors',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'authors' ) )
		);
		// custom urls.
		\register_setting(
			'xmlsf_advanced',
			'xmlsf_urls',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'custom_urls_settings' ) )
		);
		// custom sitemaps.
		\register_setting(
			'xmlsf_advanced',
			'xmlsf_custom_sitemaps',
			array( 'sanitize_callback' => array( __NAMESPACE__ . '\Sanitize', 'custom_sitemaps_settings' ) )
		);

		// Settings ACTIONS & CHECKS.
		\add_action( 'update_option_xmlsf_server', array( __CLASS__, 'update_server' ) );
		\add_action( 'update_option_xmlsf_disabled_providers', array( __CLASS__, 'update_disabled_providers' ), 10, 2 );
		\add_action( 'update_option_xmlsf_post_types', array( __CLASS__, 'update_post_types' ), 10, 2 );
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
