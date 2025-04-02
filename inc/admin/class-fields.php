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
class Fields {

	/**
	 * Server field
	 */
	public static function server_field() {
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
	public static function disable_fields() {
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
		$public_tax = \get_taxonomies( array( 'public' => true ) );
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
	public static function post_types_general_fields() {
		$defaults   = \XMLSF\get_default_settings();
		$post_types = (array) \get_option( 'xmlsf_post_types', $defaults['post_types'] );
		$settings   = (array) \get_option( 'xmlsf_post_type_settings', $defaults['post_type_settings'] );
		$limit      = ! empty( $settings['limit'] ) ? $settings['limit'] : '';

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-post-types.php';
		\xmlsf()->sitemap->uses_core_server() && include XMLSF_DIR . '/views/admin/field-sitemap-post-types-limit.php';
	}

	/**
	 * Post types field
	 *
	 * @param string $post_type Post type.
	 */
	public static function post_type_fields( $post_type ) {
		// post type slug passed as section name.
		$obj     = \get_post_type_object( $post_type );
		$count   = \wp_count_posts( $obj->name );
		$options = (array) \get_option( 'xmlsf_post_type_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-post-type-settings.php';
	}

	/**
	 * Taxonomy settings field
	 */
	public static function taxonomy_settings_field() {
		$taxonomy_settings = (array) \get_option( 'xmlsf_taxonomy_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomy-settings.php';
	}

	/**
	 * Taxonomies field
	 */
	public static function taxonomies_field() {
		$taxonomies = (array) \get_option( 'xmlsf_taxonomies', array() );
		$public_tax = (array) \get_taxonomies( array( 'public' => true ) );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomies.php';
	}

	/**
	 * Author settings field
	 */
	public static function author_settings_field() {
		$author_settings = (array) \get_option( 'xmlsf_author_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-author-settings.php';
	}

	/**
	 * Authors field
	 */
	public static function authors_field() {
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
	 * Sitemap name field
	 */
	public static function xmlsf_sitemap_name_field() {
		$sitemaps = (array) \get_option( 'xmlsf_sitemaps', array() );
		$slug     = \is_object( \xmlsf()->sitemap ) ? \xmlsf()->sitemap->slug() : ( \xmlsf()->sitemap->uses_core_server() ? 'wp-sitemap' : 'sitemap' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-name.php';
	}

	/**
	 * Custom sitemap field
	 */
	public static function custom_sitemaps_settings_field() {
		$custom_sitemaps = \get_option( 'xmlsf_custom_sitemaps' );
		$lines           = \is_array( $custom_sitemaps ) ? \implode( PHP_EOL, $custom_sitemaps ) : $custom_sitemaps;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-custom.php';
	}

	/**
	 * Custom URLs field
	 */
	public static function urls_settings_field() {
		$urls  = \get_option( 'xmlsf_urls' );
		$lines = array();

		if ( \is_array( $urls ) && ! empty( $urls ) ) {
			foreach ( $urls as $arr ) {
				if ( \is_array( $arr ) ) {
					$lines[] = \implode( ' ', $arr );
				}
			}
		}

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-urls.php';
	}

	/**
	 * Advanced archive field option
	 */
	public static function advanced_archive_field_options() {
		?>
		<option value=""<?php echo \disabled( true ); ?>>
			<?php \esc_html_e( 'Week', 'xml-sitemap-feed' ); ?>
		</option>
		<?php
	}

	/**
	 * Quick edit fields allows to add HTML in Quick Edit.
	 *
	 * @since 5.7
	 *
	 * @param string $column_name Column name.
	 */
	public static function quick_edit_fields( $column_name ) {
		if ( 'xmlsf_exclude' === $column_name ) {
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-quick-edit.php';
		}
	}

	/**
	 * Bulk edit fields allows to add HTML in Quick Edit.
	 *
	 * @since 5.7
	 *
	 * @param string $column_name Column name.
	 */
	public static function bulk_edit_fields( $column_name ) {
		if ( 'xmlsf_exclude' === $column_name ) {
			$disabled = ! apply_filters( 'xmlsf_advanced_enabled', false );
			// The actual fields for data entry.
			include XMLSF_DIR . '/views/admin/field-bulk-edit.php';
		}
	}
}
