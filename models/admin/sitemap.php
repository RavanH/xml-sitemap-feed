<?php

class XMLSF_Admin_Sitemap_Sanitize
{
	public static function taxonomies( $new )
	{
		return $new;
	}

	public static function taxonomy_settings( $new )
	{
		setlocale( LC_NUMERIC, 'C' );

		$sanitized = array();

		$sanitized['active'] = !empty($new['active']) ? '1' : '';
		$sanitized['priority'] = isset($new['priority']) ? xmlsf_sanitize_priority( str_replace( ',', '.', $new['priority'] ), '0.1', '0.9' ) : '0.3';
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';
		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : 5000;
		if ( $sanitized['term_limit'] < 1 || $sanitized['term_limit'] > 50000 ) $sanitized['term_limit'] = 50000;

		return $sanitized;
	}

	public static function post_types_settings( $new = array() )
	{
		$sanitized = is_array($new) ? $new : array();

		foreach ($sanitized as $post_type => $settings) {
			setlocale( LC_NUMERIC, 'C' );
			$sanitized[$post_type]['priority'] = isset($settings['priority']) ? xmlsf_sanitize_priority( str_replace( ',', '.', $settings['priority'] ), '0.1', '0.9' ) : '0.5';
		}

		return $sanitized;
	}

	public static function custom_sitemaps_settings( $new )
	{
		// clean up input
		if ( is_array( $new ) ) {
			$new = array_filter($new);
			$new = reset($new);
		}

		if ( empty($new) )
			return '';

		// build sanitized output
		$input = explode( PHP_EOL, sanitize_textarea_field( $new ) );
		$sanitized = array();
		foreach ( $input as $line ) {
			$line = filter_var( esc_url( trim( $line ) ), FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED );
			if ( ! empty( $line ) )
				$sanitized[] = $line;
		}

		return !empty($sanitized) ? $sanitized : '';
	}

	public static function custom_urls_settings( $new )
	{
		// clean up input
		if ( is_array( $new ) ) {
			$new = array_filter($new);
			$new = reset($new);
		}

		if ( empty($new) )
			return '';

		$input = explode( PHP_EOL, sanitize_textarea_field( $new ) );

		// build sanitized output
		$sanitized = array();
		foreach ( $input as $line ) {
			if ( empty( $line ) )
				continue;

			$arr = explode( " ", trim( $line ) );

			$url = filter_var( esc_url( trim( $arr[0] ) ), FILTER_VALIDATE_URL );

			if ( !empty( $url ) ) {
				setlocale( LC_NUMERIC, 'C' );
				$priority = isset( $arr[1] ) ? xmlsf_sanitize_priority( str_replace( ',', '.', $arr[1] ) ) : '0.5';
				$sanitized[] = array( $url, $priority );
			}
		}

		return !empty($sanitized) ? $sanitized : '';
	}
}
