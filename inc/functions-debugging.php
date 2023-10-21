<?php
/**
 * Debugging Functions
 *
 * @package XML Sitemap & Google News
 * @since 5.4
 */

/**
 * Error messages for ping
 *
 * @param string $se       Search engine name.
 * @param string $sitemap  Sitemap name.
 * @param string $ping_url Pinged URL.
 * @param int    $code     Response code.
 * @param string $response Response.
 */
function xmlsf_debug_ping( $se, $sitemap, $ping_url, $code, $response = '' ) {
	if ( ! WP_DEBUG_LOG ) {
		return;
	}

	if ( 999 === $code ) {
		error_log( 'Ping ' . $se . ' skipped.' );
	} else {
		error_log( 'Pinged ' . $ping_url . ' with response code: ' . $code );
	}

	if ( ! empty( $response ) ) {
		error_log( 'Response: ' . print_r( $response, true ) );
	}
}
add_action( 'xmlsf_ping', 'xmlsf_debug_ping', 9, 5 );

/**
 * Error messages for Nginx Helper Purge URLs
 *
 * @param array $urls Purged URLs.
 */
function xmlsf_debug_nginx_helper_purge_urls( $urls ) {
	if ( ! WP_DEBUG_LOG ) {
		return;
	}

	error_log( 'NGINX Helper purge urls array:' );
	error_log( print_r( $urls, true ) );
}
add_action( 'xmlsf_nginx_helper_purge_urls', 'xmlsf_debug_nginx_helper_purge_urls' );

/**
 * Error messages for output compression.
 */
function xmlsf_debug_output_compression() {
	if ( ! WP_DEBUG_LOG ) {
		return;
	}

	// Zlib.
	$zlib = ini_get( 'zlib.output_compression' ) ? 'ENABLED' : 'DISABLED';
	error_log( 'Zlib output compression ' . $zlib );

	// Ob_gzhandler.
	$gz = in_array( 'ob_gzhandler', ob_list_handlers(), true ) ? 'ENABLED' : 'DISABLED';
	error_log( 'GZhandler output buffer compression ' . $gz );
}
add_action( 'xmlsf_output_compression', 'xmlsf_debug_output_compression' );


/**
 * Usage info for debugging
 *
 * @since 5.4
 * @return void
 */
function xmlsf_debug_usage() {
	global $wpdb, $EZSQL_ERROR;

	$num   = get_num_queries();
	$limit = ini_get( 'memory_limit' );

	// Query errors.
	$err = 'None encountered.';
	if ( is_array( $EZSQL_ERROR ) && count( $EZSQL_ERROR ) ) {
		$err = '';
		$i   = 1;
		foreach ( $EZSQL_ERROR as $e ) {
			$err .= PHP_EOL . $i . ': ' . implode( PHP_EOL, $e ) . PHP_EOL;
			++$i;
		}
	}
	// Saved queries.
	if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
		$saved = PHP_EOL . print_r( $wpdb->queries, true );
	} else {
		$saved = 'Set SAVEQUERIES to show saved database queries here.';
	}
	// Memory usage.
	if ( function_exists( 'memory_get_peak_usage' ) ) {
		$mem = round( memory_get_peak_usage() / 1024 / 1024, 2 ) . 'M';
	} else {
		$mem = 'Not availabe.';
	}
	// System load.
	if ( function_exists( 'sys_getloadavg' ) ) {
		$load = sys_getloadavg()[0];
	} else {
		$load = 'Not available.';
	}

	echo '<!-- Queries executed: ' . htmlentities( $num, ENT_COMPAT, get_bloginfo( 'charset' ) ) . ' | Peak memory usage: ' . htmlentities( $mem, ENT_COMPAT, get_bloginfo( 'charset' ) ) . '| Memory limit: ' . htmlspecialchars( $limit, ENT_COMPAT, get_bloginfo( 'charset' ) ) . ' -->' . PHP_EOL;
	echo '<!-- Query errors: ' . htmlentities( $err, ENT_COMPAT, get_bloginfo( 'charset' ) ) . ' -->' . PHP_EOL;
	echo '<!-- Queries: ' . htmlentities( $saved, ENT_COMPAT, get_bloginfo( 'charset' ) ) . ' -->' . PHP_EOL;
	echo '<!-- Average system load during the last minute: ' . htmlentities( $load[0], ENT_COMPAT, get_bloginfo( 'charset' ) ) . ' -->' . PHP_EOL;
}

add_action(
	'xmlsf_sitemap_loaded',
	function () {
		if ( ! is_admin() && current_user_can( 'manage_options' ) ) {
			add_action( 'shutdown', 'xmlsf_debug_usage' );
		}
	}
);

add_action(
	'xmlsf_install',
	function () {
		// Kilroy was here.
		WP_DEBUG_LOG && error_log( 'XML Sitemap Feeds version ' . XMLSF_VERSION . ' installed.' );
	}
);

add_action(
	'xmlsf_upgrade',
	function ( $db_version ) {
		// Kilroy was here.
		WP_DEBUG_LOG && error_log( 'XML Sitemap Feeds upgraded from ' . $db_version . ' to ' . XMLSF_VERSION );
	}
);
