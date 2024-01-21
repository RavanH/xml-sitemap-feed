<?php
/**
 * Settings Sanitization
 *
 * @package XML Sitemap & Google News
 */

/**
 * Sanitization Class
 */
class XMLSF_Admin_Sitemap_Sanitize {

	/**
	 * Sanitize general settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function general_settings( $save ) {
		setlocale( LC_NUMERIC, 'C' );

		$sanitized       = xmlsf()->defaults( 'general_settings' );
		$save            = (array) $save;
		$allowed_servers = array(
			'core',
			'plugin',
		);

		// Sanitize server setting.
		if ( ! empty( $save['server'] ) && in_array( $save['server'], $allowed_servers, true ) ) {
			$sanitized['server'] = $save['server'];
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

		// When sitemap server has been changed, ask for rewrite rules to be REGENERATED.
		$old = (array) get_option( 'xmlsf_general_settings' );
		if ( empty( $old['server'] ) || $old['server'] !== $sanitized['server'] ) {
			delete_option( 'rewrite_rules' );
		}

		return $sanitized;
	}

	/**
	 * Sanitize taxonomies
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function taxonomies( $save ) {
		// Nothing to do really...

		return $save;
	}

	/**
	 * Sanitize taxonomies settings
	 *
	 * Clears the term_modified data from the database when settings have changed.
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function taxonomy_settings( $save ) {
		setlocale( LC_NUMERIC, 'C' );
		$sanitized = xmlsf()->defaults( 'taxonomy_settings' );
		$save      = (array) $save;

		$sanitized['active']           = ! empty( $save['active'] ) ? '1' : '';
		$sanitized['dynamic_priority'] = ! empty( $save['dynamic_priority'] ) ? '1' : '';

		// Sanitize priority.
		if ( ! empty( $save['priority'] ) && is_numeric( $save['priority'] ) ) {
			$sanitized['priority'] = xmlsf_sanitize_number( $save['priority'], .1, .9 );
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

		// Clear term meta cache if deactivating...
		if ( empty( $sanitized['active'] ) ) {
			$old = (array) get_option( 'xmlsf_taxonomy_settings', array() );
			if ( ! empty( $old['active'] ) ) {
				xmlsf_clear_metacache( 'terms' );
				// global $wpdb;
				// $wpdb->delete( $wpdb->prefix . 'termmeta', array( 'meta_key' => 'term_modified' ) );.
			}
		}

		return $sanitized;
	}

	/**
	 * Sanitize author settings
	 *
	 * Clears the term_modified data from the database when settings have changed.
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function author_settings( $save ) {
		setlocale( LC_NUMERIC, 'C' );
		$sanitized = xmlsf()->defaults( 'taxonomy_settings' );
		$save      = (array) $save;

		$sanitized['active']           = ! empty( $save['active'] ) ? '1' : '';
		$sanitized['dynamic_priority'] = ! empty( $save['dynamic_priority'] ) ? '1' : '';

		// Sanitize priority.
		if ( ! empty( $save['priority'] ) && is_numeric( $save['priority'] ) ) {
			$sanitized['priority'] = xmlsf_sanitize_number( $save['priority'], .1, .9 );
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

		// TODO Clear user meta cache if deactivating...

		return $sanitized;
	}

	/**
	 * Sanitize post types
	 *
	 * Clears the comment and image meta data from the database when settings have changed.

	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function post_types( $save = array() ) {
		setlocale( LC_NUMERIC, 'C' );
		$sanitized = is_array( $save ) ? $save : array();

		$old            = (array) get_option( 'xmlsf_post_types', array() );
		$clear_images   = false;
		$clear_comments = false;

		foreach ( $sanitized as $post_type => $settings ) {
			$sanitized[ $post_type ]['priority'] = is_numeric( $settings['priority'] ) ? xmlsf_sanitize_number( str_replace( ',', '.', $settings['priority'] ), .1, .9 ) : '0.5';

			// Poll for changes that warrant clearing meta data.
			if ( isset( $old[ $post_type ] ) && is_array( $old[ $post_type ] ) ) {

				if ( empty( $settings['active'] ) ) {
					if ( ! empty( $old[ $post_type ]['active'] ) ) {
						$clear_images   = true;
						$clear_comments = true;
					}
				} else {
					if ( isset( $old[ $post_type ]['tags'] ) && is_array( $old[ $post_type ]['tags'] ) && isset( $old[ $post_type ]['tags']['image'] ) && $old[ $post_type ]['tags']['image'] !== $settings['tags']['image'] ) {
						$clear_images = true;
					}
					if ( ! empty( $old[ $post_type ]['update_lastmod_on_comments'] ) && empty( $settings['update_lastmod_on_comments'] ) ) {
						$clear_comments = true;
					}
				}
			}
		}

		global $wpdb;

		// Clear images meta caches...
		if ( $clear_images ) {
			xmlsf_clear_metacache( 'images' );
			// $wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => '_xmlsf_image_attached' ) );
			// $wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => '_xmlsf_image_featured' ) );
			// update_option( 'xmlsf_images_meta_primed', array() );
		}

		// Clear comments meta caches...
		if ( $clear_comments ) {
			xmlsf_clear_metacache( 'comments' );
			// $wpdb->delete( $wpdb->prefix . 'postmeta', array( 'meta_key' => '_xmlsf_comment_date_gmt' ) );
			// update_option( 'xmlsf_comments_meta_primed', array() );
		}

		return $sanitized;
	}

	/**
	 * Sanitize custom sitemaps
	 *
	 * @param string $save Text field input.
	 *
	 * @return array
	 */
	public static function custom_sitemaps_settings( $save ) {
		if ( empty( $save ) ) {
			return '';
		}

		// Build sanitized output.
		$input     = explode( PHP_EOL, sanitize_textarea_field( $save ) );
		$sanitized = array();

		foreach ( $input as $line ) {
			$line = filter_var( esc_url( trim( $line ) ), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED );
			if ( ! empty( $line ) ) {
				$sanitized[] = $line;
			}
		}

		return ! empty( $sanitized ) ? $sanitized : '';
	}

	/**
	 * Sanitize custom URLs
	 *
	 * @param string $save Text field input.
	 *
	 * @return array
	 */
	public static function custom_urls_settings( $save ) {
		setlocale( LC_NUMERIC, 'C' );

		if ( empty( $save ) ) {
			return '';
		}

		$input = explode( PHP_EOL, wp_strip_all_tags( $save ) );

		// Build sanitized output.
		$sanitized = array();
		foreach ( $input as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$arr = explode( ' ', trim( $line ) );

			$url = filter_var( esc_url( trim( $arr[0] ) ), FILTER_VALIDATE_URL );

			if ( ! empty( $url ) ) {
				$priority    = isset( $arr[1] ) ? xmlsf_sanitize_number( str_replace( ',', '.', $arr[1] ) ) : '0.5';
				$sanitized[] = array( $url, $priority );
			}
		}

		return ! empty( $sanitized ) ? $sanitized : '';
	}
}
