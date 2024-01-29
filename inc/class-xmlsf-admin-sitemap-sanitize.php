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
		$defaults = xmlsf()->defaults( 'general_settings' );
		$save     = (array) $save;
		$old      = (array) get_option( 'xmlsf_general_settings' );

		// Sanitize server setting.
		$sanitized['server'] = isset( $save['server'] ) && in_array( $save['server'], array( 'core', 'plugin' ), true ) ? $save['server'] : $defaults['server'];

		// Sanitize includes.
		$sanitized['disabled'] = isset( $save['disabled'] ) && is_array( $save['disabled'] ) ? $save['disabled'] : array();

		// When sitemap server has been changed...
		if ( empty( $old['server'] ) || $old['server'] !== $sanitized['server'] ) {
			// Flush rewrite rules on next init.
			set_transient( 'xmlsf_flush_rewrite_rules', true );
		}

		// When taxonomies have been disabled...
		if ( in_array( 'taxonomies', $sanitized['disabled'], true ) && ! in_array( 'taxonomies', $old['disabled'], true ) ) {
			xmlsf_clear_metacache( 'terms' );
		}

		// TODO Clear user meta cache if deactivating...

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
		$sanitized = (array) $save;

		// Sanitize priority.
		if ( ! empty( $sanitized['priority'] ) && is_numeric( $sanitized['priority'] ) ) {
			$sanitized['priority'] = xmlsf_sanitize_number( $sanitized['priority'], .1, .9 );
		}

		// Sanitize limit.
		if ( ! empty( $sanitized['limit'] ) && is_numeric( $sanitized['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $sanitized['limit'], 1, 50000, false );
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

		$sanitized['dynamic_priority'] = ! empty( $save['dynamic_priority'] ) ? '1' : '';

		// Sanitize priority.
		if ( ! empty( $save['priority'] ) && is_numeric( $save['priority'] ) ) {
			$sanitized['priority'] = xmlsf_sanitize_number( $save['priority'], .1, .9 );
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

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

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

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
		}

		// Clear comments meta caches...
		if ( $clear_comments ) {
			xmlsf_clear_metacache( 'comments' );
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

	/**
	 * Sanitize domain settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function domains_settings( $save ) {
		$default = wp_parse_url( home_url(), PHP_URL_HOST );
		$save    = sanitize_textarea_field( $save );
		$input   = $save ? explode( PHP_EOL, wp_strip_all_tags( $save ) ) : array();

		// Build sanitized output.
		$sanitized = array();
		foreach ( $input as $line ) {
			$line = trim( $line );

			// Skip if empty line.
			if ( empty( $line ) ) {
				continue;
			}

			// Prevent parse_url misdiagnosing a domain without protocol as a path.
			$domain = strpos( $line, '://' ) === false && substr( $line, 0, 1 ) !== '/' ? '//' . $line : $line;

			// Parse url.
			$domain = wp_parse_url( filter_var( $domain, FILTER_SANITIZE_URL ), PHP_URL_HOST );

			// Empty result. Skip.
			if ( empty( $domain ) ) {
				$line = strlen( $line ) > 13 ? substr( $line, 0, 10 ) . '&hellip;' : $line;
				add_settings_error(
					'invalid_domain_notice',
					'invalid_domain',
					sprintf( /* translators: %s the first 13 characters of an entry. */
						esc_html__( 'The line "%s" did not appear to contain a valid domain.', 'xml-sitemap-feed' ),
						esc_html( $line )
					),
					'warning'
				);
				continue;
			}
			// Default (sub-)domain.
			if ( $domain === $default || strpos( $domain, '.' . $default ) !== false ) {
				add_settings_error(
					'invalid_domain_notice',
					'invalid_domain',
					sprintf( /* translators: %s the first 13 characters of an entry. */
						esc_html__( 'The domain "%s" is already an allowed domain.', 'xml-sitemap-feed' ),
						esc_html( $line )
					),
					'warning'
				);
				continue;
			}
			$sanitized[] = $domain;
		}

		return ( ! empty( $sanitized ) ) ? $sanitized : '';
	}
}
