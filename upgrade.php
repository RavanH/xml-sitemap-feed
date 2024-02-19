<?php
/**
 * XML Sitemap Feed upgrade routines
 *
 * @package XML Sitemap & Google News
 *
 * @since 5.1
 */

defined( 'WPINC' ) || die;

if ( $db_version ) {
	xmlsf_upgrade( $db_version );
} else {
	xmlsf_install();
}

update_option( 'xmlsf_version', XMLSF_VERSION );

// Flush rewrite rules on next init.
delete_option( 'rewrite_rules' );

/**
 * Update from defaults.
 *
 * @since 5.1
 *
 * @param bool $update Wether to add or update options.
 */
function xmlsf_update_from_defaults( $update = true ) {
	// Options that need not be autoloaded.
	$not_autoload = array( 'robots' );

	foreach ( xmlsf()->defaults() as $option => $default ) {
		if ( $update ) {
			update_option( 'xmlsf_' . $option, $default, '', ! in_array( $option, $not_autoload, true ) );
		} else {
			add_option( 'xmlsf_' . $option, $default, '', ! in_array( $option, $not_autoload, true ) );
		}
	}
}

/**
 * Set up default plugin data.
 *
 * @since 5.1
 */
function xmlsf_install() {
	// Add or update all defaults.
	xmlsf_update_from_defaults();

	do_action( 'xmlsf_install' );
}

/**
 * Upgrade plugin data.
 *
 * @param int $db_version Database version.
 * @since 5.1
 */
function xmlsf_upgrade( $db_version ) {
	global $wpdb;

	if ( version_compare( '4.4', $db_version, '>' ) ) {
		// Remove robots.txt rules blocking stylesheets.
		$robot_rules = get_option( 'xmlsf_robots' );
		if ( ! empty( $robot_rules ) ) {
			$robot_rules = str_replace( array( 'Disallow: */wp-content/', 'Allow: */wp-content/uploads/' ), '', $robot_rules );
			delete_option( 'xmlsf_robots' );
			add_option( 'xmlsf_robots', $robot_rules, '', false );
		}

		// Make sure custom sitemaps is an array.
		$urls = get_option( 'xmlsf_custom_sitemaps' );
		if ( ! is_array( $urls ) ) {
			$urls = explode( PHP_EOL, $urls );
			update_option( 'xmlsf_custom_sitemaps', $urls );
		}

		// Register location taxonomies then delete all terms.
		$taxonomies = array( 'gn-location-3', 'gn-location-2', 'gn-location-1' );
		foreach ( $taxonomies as $taxonomy ) {
			register_taxonomy( $taxonomy, null );
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);
			foreach ( (array) $terms as $term ) {
				wp_delete_term( $term->term_id, 'gn-location-3' );
			}
		}
	}

	if ( version_compare( '5.0.1', $db_version, '>' ) ) {
		// Delete all taxonomy terms.
		register_taxonomy( 'gn-genre', null );

		$terms = get_terms(
			array(
				'taxonomy'   => 'gn-genre',
				'hide_empty' => false,
			)
		);

		foreach ( (array) $terms as $term ) {
			wp_delete_term( $term->term_id, 'gn-genre' );
		}

		// New taxonomy settings.
		$taxonomies = get_option( 'xmlsf_taxonomies' );
		if ( empty( $taxonomies ) ) {
			$active = '';
		} else {
			$available = 0;
			$checked   = count( $taxonomies );
			foreach ( (array) get_option( 'xmlsf_post_types' ) as $post_type => $settings ) {
				if ( empty( $settings['active'] ) ) {
					continue;
				}
				$taxonomies = get_object_taxonomies( $post_type, 'objects' );
				// Check each tax public flag and term count and append name to array.
				foreach ( $taxonomies as $taxonomy ) {
					if ( ! empty( $taxonomy->public ) && ! in_array( $taxonomy->name, xmlsf()->disabled_taxonomies() ) ) {
						++$available;
					}
				}
			}
			if ( $checked === $available ) {
				update_option( 'xmlsf_taxonomies', '' );
			}
			$active = '1';
		}

		$taxonomy_settings = array(
			'active'           => $active,
			'priority'         => '0.3',
			'dynamic_priority' => '1',
			'limit'            => '5000',
		);
		add_option( 'xmlsf_taxonomy_settings', $taxonomy_settings );

		// Make sure no pong option remains.
		delete_option( 'xmlsf_pong' );

		// Update or create robots option.
		$robots = get_option( 'xmlsf_robots', '' );
		delete_option( 'xmlsf_robots' );
		add_option( 'xmlsf_robots', $robots, '', false );
	}

	if ( version_compare( '5.4', $db_version, '>' ) ) {
		// Delete old transients.
		delete_transient( 'xmlsf_ping_google_sitemap_news' );
		delete_transient( 'xmlsf_ping_google_sitemap' );
		delete_transient( 'xmlsf_ping_bing_sitemap' );
		delete_transient( 'xmlsf_flush_rewrite_rules' );
		delete_transient( 'xmlsf_check_static_files' );
		delete_transient( 'xmlsf_prefetch_post_meta_failed' );
		// Remove term meta term_modified_gmt.
		delete_metadata( 'term', 0, 'term_modified_gmt', '', true );
		// Remove comments meta _xmlsf_comment_date.
		delete_metadata( 'post', 0, '_xmlsf_comment_date', '', true );

		$author_settings = (array) get_option( 'xmlsf_author_settings', array() );
		$tax_settings    = (array) get_option( 'xmlsf_taxonomy_settings', array() );
		// Do not switch to core sitemap when upgrading.
		add_option( 'xmlsf_server', 'plugin' );
		// Set include array.
		$disabled = array();
		if ( empty( $tax_settings['active'] ) ) {
			$disabled[] = 'taxonomies';
		}
		if ( empty( $author_settings['active'] ) ) {
			$disabled[] = 'users';
		}
		// Add general settings option.
		add_option( 'xmlsf_disabled_providers', ! empty( $disabled ) ? $disabled : '' );
		// Update taxonomy terms limit.
		$tax_settings['limit'] = isset( $tax_settings['term_limit'] ) ? $tax_settings['term_limit'] : '3000';
		unset( $tax_settings['term_limit'] );
		update_option( 'xmlsf_taxonomy_settings', $tax_settings );
		// Update users limit.
		$author_settings['limit'] = isset( $author_settings['term_limit'] ) ? $author_settings['term_limit'] : '1000';
		unset( $author_settings['term_limit'] );
		update_option( 'xmlsf_author_settings', $author_settings );

		// Delete old settings.
		delete_option( 'xmlsf_ping' );
		delete_option( 'xmlsf_permalinks_flushed' );
		delete_option( 'xmlsf_domains' );
		set_transient( 'xmlsf_images_meta_primed', get_option( 'xmlsf_images_meta_primed' ) );
		set_transient( 'xmlsf_comments_meta_primed', get_option( 'xmlsf_comments_meta_primed' ) );
		delete_option( 'xmlsf_images_meta_primed' );
		delete_option( 'xmlsf_comments_meta_primed' );

		// Remove deprecated transient.
		delete_transient( 'xmlsf_static_files' );
	}

	// Add missing new defaults.
	xmlsf_update_from_defaults( false );

	do_action( 'xmlsf_upgrade', $db_version );
}
