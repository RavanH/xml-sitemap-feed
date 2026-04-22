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
class Sitemap_Fields {

	/**
	 * Server field
	 */
	public static function server() {
		$server       = \get_option( 'xmlsf_server' );
		$server       = ! \in_array( $server, array( 'core', 'plugin' ), true ) ? \XMLSF\get_default_settings( 'server' ) : $server;
		$nosimplexml  = ! \class_exists( 'SimpleXMLElement' );
		$nocoreserver = ! \function_exists( 'get_sitemap_url' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-server.php';
	}

	/**
	 * Deactivate fields
	 */
	public static function disable() {
		$post_types = \get_post_types( array( 'public' => true ) );
		// We're not supporting sitemaps for author pages for attachments and pages.
		unset( $post_types['attachment'] );
		unset( $post_types['page'] );

		/**
		 * Filters the has_published_posts query argument in the author archive. Must return a boolean or an array of one or multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 5.4
		 *
		 * @param array Array with post type slugs. Default array( 'post' ).
		 *
		 * @return mixed
		 */
		$post_types = \apply_filters( 'xmlsf_author_has_published_posts', $post_types );

		$disabled   = (array) \get_option( 'xmlsf_disabled_providers', \XMLSF\get_default_settings( 'disabled_providers' ) );
		$public_tax = \get_taxonomies( array( 'publicly_queryable' => true ) );
		$users_args = array(
			'fields'              => 'ID',
			'has_published_posts' => $post_types,
		);

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-disable.php';
	}

	/**
	 * Limit field
	 */
	public static function post_types_general() {
		$defaults   = \XMLSF\get_default_settings();
		$post_types = (array) \get_option( 'xmlsf_post_types', $defaults['post_types'] );
		$settings   = (array) \get_option( 'xmlsf_post_type_settings', $defaults['post_type_settings'] );
		$limit      = ! empty( $settings['limit'] ) ? $settings['limit'] : '';

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-post-types.php';
		'core' === \xmlsf()->sitemap->server_type && include XMLSF_DIR . '/views/admin/field-sitemap-post-types-limit.php';
	}

	/**
	 * Post types field
	 *
	 * @param string $post_type Post type.
	 */
	public static function post_type( $post_type ) {
		// post type slug passed as section name.
		$obj     = \get_post_type_object( $post_type );
		$count   = wp_count_posts( $obj->name );
		$options = (array) \get_option( 'xmlsf_post_type_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-post-type-settings.php';
	}

	/**
	 * Taxonomy settings field
	 */
	public static function taxonomy_settings() {
		$taxonomy_settings = (array) \get_option( 'xmlsf_taxonomy_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomy-settings.php';
	}

	/**
	 * Taxonomies field
	 */
	public static function taxonomies() {
		$taxonomies = (array) \get_option( 'xmlsf_taxonomies', array() );
		$public_tax = (array) \get_taxonomies( array( 'publicly_queryable' => true ), 'objects' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomies.php';
	}

	/**
	 * Author settings field
	 */
	public static function author_settings() {
		$author_settings = (array) \get_option( 'xmlsf_author_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-author-settings.php';
	}

	/**
	 * Authors field
	 */
	public static function authors() {
		$post_types = \get_post_types( array( 'public' => true ) );
		// We're not supporting sitemaps for author pages for attachments and pages.
		unset( $post_types['attachment'] );
		unset( $post_types['page'] );

		/**
		 * Filters the has_published_posts query argument in the author archive. Must return a boolean or an array of one or multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 5.4
		 *
		 * @param array Array with post type slugs. Default array( 'post' ).
		 *
		 * @return mixed
		 */
		$post_types = \apply_filters( 'xmlsf_author_has_published_posts', $post_types );

		$authors = (array) \get_option( 'xmlsf_authors', array() );
		$users   = (array) \get_users( array( 'has_published_posts' => $post_types ) );

		include XMLSF_DIR . '/views/admin/field-sitemap-authors.php';
	}

	/**
	 *  ADVANCED TAB FIELDS
	 */

	/**
	 * Sitemap slug field
	 */
	public static function xmlsf_sitemap_slug() {
		$sitemaps    = (array) \get_option( 'xmlsf_sitemaps', array() );
		$placeholder = \is_object( \xmlsf()->sitemap ) && 'core' === \xmlsf()->sitemap->server_type ? 'wp-sitemap' : 'sitemap';
		$slug        = \get_option( 'xmlsf_sitemap_name', '' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-slug.php';
	}

	/**
	 * Custom sitemap field
	 */
	public static function custom_sitemaps_settings() {
		$custom_sitemaps = \get_option( 'xmlsf_custom_sitemaps' );
		$lines           = \is_array( $custom_sitemaps ) ? \implode( PHP_EOL, $custom_sitemaps ) : $custom_sitemaps;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-custom.php';
	}

	/**
	 * Custom URLs field
	 */
	public static function urls_settings() {
		$urls  = \get_option( 'xmlsf_urls' );
		$lines = array();

		if ( \is_array( $urls ) && ! empty( $urls ) ) {
			foreach ( $urls as $url ) {
				$lines[] = ( \is_array( $url ) ) ? \implode( ' ', $url ) : $url;
			}
		}

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-urls.php';
	}

	/**
	 * Advanced archive field option
	 */
	public static function advanced_archive_options() {
		?>
		<option value="" disabled="disabled">
			<?php \esc_html_e( 'Week', 'xml-sitemap-feed' ); ?>
		</option>
		<?php
	}

	/**
	 * Quick edit fields allows to add HTML in Quick Edit.
	 *
	 * @since 5.5
	 *
	 * @param string $column_name Column name.
	 */
	public static function quick_edit( $column_name ) {
		if ( 'xmlsf_exclude' === $column_name ) {
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-quick-edit.php';
		}
	}

	/**
	 * Bulk edit fields allows to add HTML in Quick Edit.
	 *
	 * @since 5.5
	 *
	 * @param string $column_name Column name.
	 */
	public static function bulk_edit( $column_name ) {
		if ( 'xmlsf_exclude' === $column_name ) {
			$disabled = ! \apply_filters( 'xmlsf_advanced_enabled', false );
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-bulk-edit.php';
		}
	}

	/**
	 * Sitemap notifier field.
	 *
	 * @since 5.6
	 */
	public static function sitemap_notifier() {
		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-notifier.php';
	}

	/**
	 * GSC log section
	 *
	 * @since 5.7
	 */
	public static function ping_log () {
		$log = (array) get_option( 'xmlsf_notifier_log', array() );
		$advanced = apply_filters( 'xmlsf_advanced_enabled', false ) ? '' : sprintf( /* Translators: %s: XML Sitemap Advanced (with link) */ esc_html__( 'Available in %s.', 'xml-sitemap-feed' ), '<a href="https://premium.status301.com/downloads/xml-sitemap-advanced/" target="_blank">' . esc_html__( 'XML Sitemap Advanced', 'xml-sitemap-feed' ) . '</a>' );

		include XMLSF_DIR . '/views/admin/section-ping-log.php';
	}
}
