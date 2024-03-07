<?php
/**
 * Autoloader
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF;

/**
 * Register XMLSF autoloader
 * http://justintadlock.com/archives/2018/12/14/php-namespaces-for-wordpress-developers
 *
 * @since 5.5
 *
 * @param string $class_name Namespaced class name.
 */
function autoloader( $class_name ) {
	// Bail if the class is not in our namespace.
	if ( 0 !== \strpos( $class_name, 'XMLSF\\' ) ) {
		return;
	}

	// Build the filename.
	$class_name = \str_replace( 'XMLSF\\', '', $class_name );
	$class_name = \strtolower( $class_name );
	$class_name = \str_replace( '_', '-', $class_name );
	$file       = \realpath( XMLSF_DIR ) . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'class-' . $class_name . '.php';

	// If the file exists for the class name, load it.
	if ( file_exists( $file ) ) {
		include $file;
	}
}

spl_autoload_register( 'XMLSF\autoloader' );
