<?php
/* ------------------------------
 *        Conroller CLASS
 * ------------------------------ */

class XMLSitemapFeed_Controller {

	/**
	* Sitemap instance
	* @var object
	*/
	public static $xmlsf;

	/**
	* News sitemap instance
	* @var object
	*/
	public static $xmlsf_news;


	/**
	* METHODS
	*/


	/**
	 * Ping
	 *
	 * @param $uri
	 * @param int $timeout
	 *
	 * @return bool
	 */
	public function ping( $uri, $timeout = 3 ) {
		$response = wp_remote_request( $uri, array('timeout'=>$timeout) );

		if ( defined('WP_DEBUG') && WP_DEBUG ) {
			error_log( 'PING: '.$uri );
			error_log( print_r( $response, true ) );
		}

		$code = wp_remote_retrieve_response_code( $response );

		return !empty($code) ? $code : '999';
	}

	/**
	* Initialize
	*/
	public function init() {
		// upgrade or install
		$db_version = get_option( 'xmlsf_version', 0 );

		if ( version_compare( XMLSF_VERSION, $db_version, '>' ) ) {
			// don't flush rules from init as Polylang chokes on that
			// just remove the db option and let WP regenerate them when ready...
			delete_option( 'rewrite_rules' );
			// ... but make sure rules are regenerated when admin is visited.
			set_transient( 'xmlsf_flush_rewrite_rules', '' );
			// static files checking
			set_transient( 'xmlsf_check_static_files', '' );

			$this->upgrade( $db_version );

			if ( defined('WP_DEBUG') && WP_DEBUG ) {
				error_log('XML Sitemap Feeds upgraded from '.$version.' to '.XMLSF_VERSION);
			}
		}

		// include sitemaps if any enabled
		if ( !empty( get_option( 'xmlsf_sitemaps' ) ) ) {
			xmlsf();
		}
	}

	/**
	 * Upgrade
	 */
	public function upgrade( $old_version ) {
		// upgrade or install
		if ( $old_version ) :

			if ( version_compare( '5.0', $old_version, '>' ) ) {
				// delete all taxonomy terms
				register_taxonomy( 'gn-genre', null );

				$terms = get_terms( 'gn-genre', array( 'hide_empty' => false ) );

				if ( is_array( $terms ) )
					foreach ( $terms as $term )
						wp_delete_term(	$term->term_id, 'gn-genre' );

				// recreate each db opion
				$defaults = xmlsf()->defaults();
				$autoload = xmlsf()->autoload;
				foreach ( $defaults as $option => $default ) {
					$value = get_option( 'xmlsf_'.$option, $default );
					delete_option( 'xmlsf_'.$option );
					if ( in_array( $option, $autoload ) )
						add_option( 'xmlsf_'.$option, $value );
					else
						add_option( 'xmlsf_'.$option, $value, null, 'no' );
				}
			}

			if ( version_compare( '4.4', $old_version, '>' ) ) {
				// remove robots.txt rules blocking stylesheets
			 	if ( $robot_rules = get_option( 'xmlsf_robots' ) ) {
					$robot_rules = str_replace( array('Disallow: */wp-content/','Allow: */wp-content/uploads/'), '', $robot_rules );
					delete_option( 'xmlsf_robots' );
					add_option( 'xmlsf_robots', $robot_rules, '', 'no' );
				}

				delete_option( 'xmlsf_pong' );
			}

			if ( version_compare( '4.4.1', $old_version, '>' ) ) {
				// register location taxonomies then delete all terms
				register_taxonomy( 'gn-location-3', null );
				$terms = get_terms( 'gn-location-3', array('hide_empty' => false) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-3' );
				}

				register_taxonomy( 'gn-location-2', null );
				$terms = get_terms( 'gn-location-2',array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-2' );
				}

				register_taxonomy( 'gn-location-1', null );
				$terms = get_terms( 'gn-location-1',array( 'hide_empty' => false ) );
				foreach ( $terms as $term ) {
					wp_delete_term(	$term->term_id, 'gn-location-1' );
				}
			};

		else :

			$defaults = xmlsf()->defaults();
			$autoload = xmlsf()->autoload;

			// recreate each db opion
			$defaults = xmlsf()->defaults();
			$autoload = xmlsf()->autoload;
			foreach ( $defaults as $option => $default ) {
				if ( in_array( $option, $autoload ) )
					add_option( 'xmlsf_'.$option, $default );
				else
					add_option( 'xmlsf_'.$option, $default, null, 'no' );
			};

		endif;

		update_option( 'xmlsf_version', XMLSF_VERSION );
	}

	/**
	 * Usage info for debugging
	 */
	public static function _e_usage() {
		if ( defined('WP_DEBUG') && WP_DEBUG == true ) {
			$num = get_num_queries();
			$mem = function_exists('memory_get_peak_usage') ? round(memory_get_peak_usage()/1024/1024,2) : 0;

			require XMLSF_DIR . '/view/_usage.php';
		}
	}

	public function activate() {
		delete_option( 'rewrite_rules' );
		set_transient( 'xmlsf_flush_rewrite_rules', '' );
		set_transient( 'xmlsf_check_static_files', '' );
	}

	public function deactivate() {
		delete_option( 'rewrite_rules' );
		delete_transient( 'xmlsf_flush_rewrite_rules' );
		delete_transient( 'xmlsf_check_static_files' );
		delete_transient( 'xmlsf_clear_settings' );
	}

	/**
	* CONSTRUCTOR
	*/

	function __construct() {
		register_activation_hook( __FILE__, array($this, 'activate') );
		register_deactivation_hook( __FILE__, array($this, 'deactivate') );

		add_action( 'init', array($this, 'init'), 0 );
	}
}
