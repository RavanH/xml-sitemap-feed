<?php
/**
 * Settings Sanitization
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Admin;

/**
 * Sanitization Class
 */
class Sanitize {

	/**
	 * Sanitize server setting
	 *
	 * @param string $save Setting.
	 *
	 * @return string
	 */
	public static function server( $save ) {
		$sanitized = empty( $save ) || ! \in_array( $save, array( 'core', 'plugin' ), true ) ? \XMLSF\get_default_settings( 'server' ) : $save;

		return $sanitized;
	}

	/**
	 * Sanitize server setting
	 *
	 * @param mixed $save Settings array or empty value.
	 *
	 * @return mixed
	 */
	public static function disabled_providers( $save ) {
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
		$save = (array) $save;

		// Sanitize priority.
		if ( ! empty( $save['priority'] ) && \is_numeric( $save['priority'] ) ) {
			$save['priority'] = \XMLSF\sanitize_number( $save['priority'], .1, .9 );
		} else {
			$sanitized['priority'] = '';
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && \is_numeric( $save['limit'] ) ) {
			$save['limit'] = \XMLSF\sanitize_number( $save['limit'], 1, 50000, 0 );
		}

		return $save;
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
		$save = (array) $save;

		$save['dynamic_priority'] = ! empty( $save['dynamic_priority'] ) ? '1' : '';

		// Sanitize priority.
		if ( ! empty( $save['priority'] ) && \is_numeric( $save['priority'] ) ) {
			$save['priority'] = \XMLSF\sanitize_number( $save['priority'], .1, .9 );
		} else {
			$save['priority'] = '';
		}

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && \is_numeric( $save['limit'] ) ) {
			$save['limit'] = \XMLSF\sanitize_number( $save['limit'], 1, 50000, 0 );
		}

		return $save;
	}

	/**
	 * Sanitize post types
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function post_types( $save ) {
		return $save;
	}

	/**
	 * Sanitize post type settings
	 *
	 * @param array $save Settings array.
	 *
	 * @return array
	 */
	public static function post_type_settings( $save = array() ) {
		$save = (array) $save;

		// Sanitize limit.
		if ( ! empty( $save['limit'] ) && \is_numeric( $save['limit'] ) ) {
			$save['limit'] = \XMLSF\sanitize_number( $save['limit'], 1, 50000, 0 );
		}

		// Sanitize priorities.
		foreach ( $save as $post_type => $settings ) {
			if ( ! is_array( $settings ) ) {
				continue;
			}

			if ( ! empty( $settings['priority'] ) && \is_numeric( $settings['priority'] ) ) {
				$save[ $post_type ]['priority'] = \XMLSF\sanitize_number( $settings['priority'], .1, .9 );
			} else {
				$save[ $post_type ]['priority'] = '';
			}
		}

		return $save;
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
		$input     = \explode( PHP_EOL, sanitize_textarea_field( $save ) );
		$sanitized = array();

		foreach ( $input as $line ) {
			$line = \filter_var( \esc_url( \trim( $line ) ), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED );
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
		\setlocale( LC_NUMERIC, 'C' );

		if ( empty( $save ) ) {
			return '';
		}

		$input = \explode( PHP_EOL, wp_strip_all_tags( $save ) );

		// Build sanitized output.
		$sanitized = array();
		foreach ( $input as $line ) {
			if ( empty( $line ) ) {
				continue;
			}

			$arr = \explode( ' ', trim( $line ) );

			$url = \filter_var( \esc_url( \trim( $arr[0] ) ), FILTER_VALIDATE_URL );

			if ( ! empty( $url ) ) {
				$priority    = isset( $arr[1] ) ? \XMLSF\sanitize_number( \str_replace( ',', '.', $arr[1] ) ) : '';
				$sanitized[] = array( $url, $priority );
			}
		}

		return ! empty( $sanitized ) ? $sanitized : '';
	}
}
