<?php

class XMLSF_Admin_Sitemap_Sanitize
{
	public static function taxonomies( $new )
	{
		$old = get_option( 'xmlsf_taxonomies' );
		if ( empty( $old ) ) $old = array();
		$diff = array_diff( (array) $old, (array) $new );
		if ( ! empty( $diff ) ) {
			global $wpdb;
			$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified' ) );
		}

		return $new;
	}

	public static function taxonomy_settings( $new )
	{
		setlocale( LC_NUMERIC, 'C' );

		$sanitized = array();

		$sanitized['active'] = !empty($new['active']) ? '1' : '';
		$sanitized['priority'] = isset($new['priority']) ? xmlsf_sanitize_priority( str_replace( ',', '.', $new['priority'] ), .1, .9 ) : '0.3';
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';
		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : 5000;
		if ( $sanitized['term_limit'] < 1 || $sanitized['term_limit'] > 50000 ) $sanitized['term_limit'] = 50000;

		// clear term meta cache if deactivating...
		if ( empty($sanitized['active']) ) {
			$old = (array) get_option( 'xmlsf_taxonomy_settings', array() );
			if ( ! empty($old['active']) ) {
				global $wpdb;
				$wpdb->delete( $wpdb->prefix.'termmeta', array( 'meta_key' => 'term_modified' ) );
			}
		}

		return $sanitized;
	}

	public static function author_settings( $new )
	{
		setlocale( LC_NUMERIC, 'C' );

		$sanitized = array();

		$sanitized['active'] = !empty($new['active']) ? '1' : '';
		$sanitized['priority'] = isset($new['priority']) ? xmlsf_sanitize_priority( str_replace( ',', '.', $new['priority'] ), .1, .9 ) : '0.3';
		$sanitized['dynamic_priority'] = !empty($new['dynamic_priority']) ? '1' : '';
		$sanitized['term_limit'] = isset($new['term_limit']) ? intval($new['term_limit']) : 5000;
		if ( $sanitized['term_limit'] < 1 || $sanitized['term_limit'] > 50000 ) $sanitized['term_limit'] = 50000;

		// clear user meta cache if deactivating...
/*		if ( empty($sanitized['active']) ) {
			$old = (array) get_option( 'xmlsf_taxonomy_settings', array() );
			if ( ! empty($old['active']) ) {
				global $wpdb;
				$wpdb->delete( $wpdb->prefix.'usermeta', array( 'meta_key' => 'last_published_date_gmt' ) );
			}
		}
*/
		return $sanitized;
	}

	public static function post_types_settings( $new = array() )
	{
		$sanitized = is_array($new) ? $new : array();

		$old = (array) get_option( 'xmlsf_post_types', array() );
		$clear_images = false;
		$clear_comments = false;

		foreach ( $sanitized as $post_type => $settings ) {
			setlocale( LC_NUMERIC, 'C' );
			$sanitized[$post_type]['priority'] = isset($settings['priority']) ? xmlsf_sanitize_priority( str_replace( ',', '.', $settings['priority'] ), .1, .9 ) : '0.5';

			// poll for changes that warrant clearing meta data
			if ( isset($old[$post_type]) && is_array($old[$post_type]) ) {

				if ( empty($settings['active']) ) {
					if ( !empty($old[$post_type]['active']) ) {
						$clear_images = true;
						$clear_comments = true;
					}
				} else {
					if ( isset($old[$post_type]['tags']) && is_array($old[$post_type]['tags']) && isset($old[$post_type]['tags']['image']) && $old[$post_type]['tags']['image'] != $settings['tags']['image'] ) {
						$clear_images = true;
					}
					if ( ! empty($old[$post_type]['update_lastmod_on_comments']) && empty($settings['update_lastmod_on_comments']) ) {
						$clear_comments = true;
					}
				}

			}
		}

		global $wpdb;

		// clear images meta caches...
		if ( $clear_images ) {
			$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_attached' ) );
			$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_image_featured' ) );
			update_option( 'xmlsf_images_meta_primed', array() );
		}

		// clear comments meta caches...
		if ( $clear_comments ) {
			$wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => '_xmlsf_comment_date_gmt' ) );
			update_option( 'xmlsf_comments_meta_primed', array() );
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

		$input = explode( PHP_EOL, strip_tags( $new ) );

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
