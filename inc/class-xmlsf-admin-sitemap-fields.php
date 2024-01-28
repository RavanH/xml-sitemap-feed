<?php
/**
 * Admin for Sitemap
 *
 * @package XML Sitemap & Google News
 */

/**
 * Admin Sitemap Class
 */
class XMLSF_Admin_Sitemap_Fields {

	/**
	 * Server field
	 */
	public static function server_field() {
		$settings    = (array) get_option( 'xmlsf_general_settings' );
		$defaults    = xmlsf()->defaults( 'general_settings' );
		$server      = ! empty( $settings['server'] ) ? $settings['server'] : $defaults['server'];
		$nosimplexml = ! class_exists( 'SimpleXMLElement' );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-server.php';
	}

	/**
	 * Include fields
	 */
	public static function include_fields() {
		/**
		 * Filters the post types present in the author archive. Must return an array of one or multiple post types.
		 * Allows to add or change post type when theme author archive page shows custom post types.
		 *
		 * @since 0.1
		 *
		 * @param array Array with post type slugs. Default array( 'post' ).
		 *
		 * @return array
		 */
		$post_type_array = apply_filters( 'xmlsf_author_post_types', array( 'post' ) );

		$settings   = (array) get_option( 'xmlsf_general_settings', xmlsf()->defaults( 'general_settings' ) );
		$disabled   = ! empty( $settings['disabled'] ) ? (array) $settings['disabled'] : array();
		$public_tax = xmlsf_public_taxonomies();
		$users_args = array(
			'fields'              => 'ID',
			'has_published_posts' => $post_type_array,
		);
		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-disable.php';
	}

	/**
	 * Limit field
	 */
	public static function post_types_general_fields() {
		$settings = (array) get_option( 'xmlsf_general_settings' );
		$defaults = xmlsf()->defaults( 'general_settings' );
		$limit    = ! empty( $settings['limit'] ) ? $settings['limit'] : $defaults['limit'];

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-limit.php';
	}

	/**
	 * Post types field
	 *
	 * @param string $post_type Post type.
	 */
	public static function post_type_fields( $post_type ) {
		// post type slug passed as section name.
		$obj     = get_post_type_object( $post_type );
		$count   = wp_count_posts( $obj->name );
		$options = (array) get_option( 'xmlsf_post_types', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-post-type-settings.php';
	}

	/**
	 * Taxonomy settings field
	 */
	public static function taxonomy_settings_field() {
		$taxonomy_settings = (array) get_option( 'xmlsf_taxonomy_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomy-settings.php';
	}

	/**
	 * Taxonomies field
	 */
	public static function taxonomies_field() {
		$taxonomies = (array) get_option( 'xmlsf_taxonomies', array() );
		$public_tax = (array) xmlsf_public_taxonomies();

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-taxonomies.php';
	}

	/**
	 * Author settings field
	 */
	public static function author_settings_field() {
		$author_settings = (array) get_option( 'xmlsf_author_settings', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-author-settings.php';
	}

	/**
	 * Authors field
	 */
	public static function authors_field() {
		include XMLSF_DIR . '/views/admin/field-sitemap-authors.php';
	}

	/**
	 *  ADVANCED TAB FIELDS
	 */

	/**
	 * Sitemap name field
	 */
	public static function xmlsf_sitemap_name_field() {
		global $wp_rewrite;
		$sitemaps = (array) get_option( 'xmlsf_sitemaps', array() );

		if ( xmlsf_uses_core_server() ) {
			$name = $wp_rewrite->using_permalinks() ? 'wp-sitemap.xml' : '?sitemap=index';
		} else {
			$name = $wp_rewrite->using_permalinks() ? 'sitemap.xml' : '?feed=sitemap';
		}

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-name.php';
	}

	/**
	 * Domain settings field
	 */
	public static function domains_settings_field() {
		$domains = (array) get_option( 'xmlsf_domains', array() );

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-domains.php';
	}

	/**
	 * Custom sitemap field
	 */
	public static function custom_sitemaps_settings_field() {
		$custom_sitemaps = get_option( 'xmlsf_custom_sitemaps' );
		$lines           = is_array( $custom_sitemaps ) ? implode( PHP_EOL, $custom_sitemaps ) : $custom_sitemaps;

		// The actual fields for data entry.
		include XMLSF_DIR . '/views/admin/field-sitemap-custom.php';
	}

	/**
	 * Custom URLs field
	 */
	public static function urls_settings_field() {
		$urls  = get_option( 'xmlsf_urls' );
		$lines = array();

		if ( is_array( $urls ) && ! empty( $urls ) ) {
			foreach ( $urls as $arr ) {
				if ( is_array( $arr ) ) {
					$lines[] = implode( ' ', $arr );
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
		<option value=""<?php echo disabled( true ); ?>>
			<?php esc_html_e( 'Week', 'xml-sitemap-feed' ); ?>
		</option>
		<?php
	}
}
