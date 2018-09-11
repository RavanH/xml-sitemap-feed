<?php

class XMLSF_Admin_Sitemap_Sanitize
{
	public static function taxonomies( $new )
	{
		return $new;
	}

	public static function taxonomy_settings( $new )
	{
		$sanitized = array();

		$sanitized['active'] = !empty($new['active']) ? '1' : '';
		$sanitized['priority'] = isset($new['priority']) ? self::priority($new['priority'],'0.1','0.9') : '0.3';
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';
		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : 5000;
		if ( $sanitized['term_limit'] < 1 || $sanitized['term_limit'] > 50000 ) $sanitized['term_limit'] = 50000;

		return $sanitized;
	}

	public static function domains_settings( $new )
	{
		$default = parse_url( home_url(), PHP_URL_HOST );

		// clean up input
		if(is_array($new)) {
		  $new = array_filter($new);
		  $new = reset($new);
		}
		$input = $new ? explode( PHP_EOL, sanitize_textarea_field( $new ) ) : array();

		// build sanitized output
		$sanitized = array();
		foreach ($input as $line) {
			$line = trim($line);
			$parsed_url = parse_url( trim( filter_var( $line, FILTER_SANITIZE_URL ) ) );
			// Before PHP version 5.4.7, parse_url will return the domain as path when scheme is omitted so we do:
			if ( !empty($parsed_url['host']) ) {
				$domain = trim( $parsed_url['host'] );
			} else {
				$domain_arr = explode('/', $parsed_url['path']);
				$domain_arr = array_filter($domain_arr);
				$domain = array_shift( $domain_arr );
				$domain = trim( $domain );
			}

			// filter out empties and default domain
			if(!empty($domain) && $domain !== $default && strpos($domain,".".$default) === false)
				$sanitized[] = $domain;
		}

		return (!empty($sanitized)) ? $sanitized : '';
	}

	public static function post_types_settings( $new = array() )
	{
		$sanitized = is_array($new) ? $new : array();

		foreach ($sanitized as $post_type => $settings) {
			$sanitized[$post_type]['priority'] = isset($settings['priority']) ? self::priority($settings['priority'],'0.1','0.9') : '0.5';
		}

		return $sanitized;
	}

	public static function priority( $priority, $min = 0, $max = 1 )
	{
		// make sure we have the proper locale setting for calculations
		setlocale( LC_NUMERIC, 'C' );

		$priority = floatval(str_replace(',','.',$priority));

		if ( $priority <= (float) $min ) {
			return number_format( $min, 1 );
		} elseif ( $priority >= (float) $max ) {
			return number_format( $max, 1 );
		} else {
			return number_format( $priority, 1 );
		}
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
		$input = explode( PHP_EOL, sanitize_textarea_field($new) );
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
				$priority = isset( $arr[1] ) ? self::priority($arr[1]) : '0.5';
				$sanitized[] = array( $url, $priority );
			}
		}

		return !empty($sanitized) ? $sanitized : '';
	}
}
