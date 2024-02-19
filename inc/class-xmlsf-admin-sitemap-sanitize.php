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
	 * Sanitize server setting
	 *
	 * @param string $save Setting.
	 *
	 * @return string
	 */
	public static function server( $save ) {
		$sanitized = empty( $save ) || ! in_array( $save, array( 'core', 'plugin' ), true ) ? xmlsf()->defaults( 'server' ) : $save;

		return $sanitized;
	}

	/**
	 * Sanitize server setting
	 *
	 * @param mixed $save Settings array or empty value.
	 *
	 * @return mixed
	 */
	public static function disabled( $save ) {
		// Nothing to do really...

		return $save;
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
	 * Sanitize authors
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function authors( $save ) {
		// Nothing to do really...

		return $save;
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

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && is_numeric( $save['limit'] ) ) {
			$sanitized['limit'] = xmlsf_sanitize_number( $save['limit'], 1, 50000, false );
		}

		foreach ( $sanitized as $post_type => $settings ) {
			$sanitized[ $post_type ]['priority'] = is_numeric( $settings['priority'] ) ? xmlsf_sanitize_number( str_replace( ',', '.', $settings['priority'] ), .1, .9 ) : '0.5';
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
