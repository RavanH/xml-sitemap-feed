<?php
/**
 * Polylang compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * Polylang compatibility class
 */
class Polylang {

	/**
	 * PLL links model.
	 *
	 * @var PLL_Links_...
	 */
	private static $links_model = null;

	/**
	 * Get Polylang translations
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	public static function get_translations( $post_id ) {
		$translation_ids = array();

		if ( \function_exists( 'pll_get_post_translations' ) ) {
			$translations = \pll_get_post_translations( $post_id );

			foreach ( $translations as $slug => $id ) {
				if ( $post_id !== $id ) {
					$translation_ids[] = $id;
				}
			}
		}

		return $translation_ids;
	}

	/**
	 * Polylang request filter
	 *
	 * @param array $request The request.
	 *
	 * @return array
	 */
	public static function filter_request( $request ) {
		if ( \xmlsf()->is_news || ( \xmlsf()->is_sitemap && ! \xmlsf()->sitemap->uses_core_server() ) ) {
			$request['lang'] = '';
		}

		return $request;
	}

	/**
	 * Polylang compatibility actions
	 *
	 * @param array $server The sitemap server.
	 *
	 * @return void
	 */
	public static function request_actions( $server ) {
		global $polylang;

		// Prevent language redirections.
		\add_filter( 'pll_check_canonical_url', '__return_false' );

		if ( isset( $polylang ) ) {
			// Remove Polylang filters to place all languages in the same sitemaps.
			'plugin' === $server && \remove_filter( 'pll_set_language_from_query', array( $polylang->sitemaps, 'set_language_from_query' ) );
			// \remove_filter( 'rewrite_rules_array', array( $polylang->sitemaps, 'rewrite_rules' ) );
			// \remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
		}
	}

	/**
	 * News publication name filter for Polylang.
	 *
	 * @param string $name Publication name.
	 * @param int    $post_id Post ID.
	 *
	 * @return string
	 */
	public static function news_name( $name, $post_id ) {
		return \function_exists( 'pll_translate_string' ) ? \pll_translate_string( $name, \pll_get_post_language( $post_id, 'locale' ) ) : $name;
	}

	/**
	 * Post language filter for Polylang.
	 *
	 * @param string $locale Locale.
	 * @param int    $post_id Post ID.
	 *
	 * @return string
	 */
	public static function post_language_filter( $locale, $post_id ) {
		return \function_exists( 'pll_get_post_language' ) ? \pll_get_post_language( $post_id, 'locale' ) : $locale;
	}

	/**
	 * Pre register news provider action.
	 */
	public static function remove_replace_provider() {
		// Polylang compatibility: prevent sitemap translations.
		global $polylang;
		if ( isset( $polylang ) && is_object( $polylang ) && property_exists( $polylang, 'sitemaps' ) && \is_object( $polylang->sitemaps ) ) {
			\remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
		}
	}

	/**
	 * Post register news provider action.
	 */
	public static function add_replace_provider() {
		global $polylang;
		if ( isset( $polylang ) && is_object( $polylang ) && property_exists( $polylang, 'sitemaps' ) && ! \has_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) ) ) {
			// Re-add Polylang filter.
			\add_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
		}
	}

	/**
	 * Root urls.
	 *
	 * @param array $data Root URL array.
	 *
	 * @return array
	 */
	public static function root_data( $data ) {
		if ( \function_exists( 'pll_languages_list' ) && \function_exists( 'pll_home_url' ) ) {
			$languages = \pll_languages_list();
			$data      = array();

			if ( is_array( $languages ) ) {
				foreach ( $languages as $language ) {
					$url          = \pll_home_url( $language );
					$data[ $url ] = array(
						'lastmod' => \get_lastpostdate( 'gmt', 'post' ), // TODO make lastmod date language specific.
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Do author archives for non-default languages
	 * Hooked into xmlsf_url_after
	 *
	 * @param string $which Which sitemap.
	 * @param obj    $user  User object.
	 * @param array  $data  User sitemap data.
	 */
	public static function author_archive_translations( $which, $user, $data = array() ) {
		if ( 'author' === $which && ! empty( $data ) && \function_exists( 'PLL' ) && \is_object( PLL() ) ) {
			$languages = \pll_languages_list(
				array(
					'hide_empty' => true,
					'fields'     => 'slug',
				)
			);
			if ( null === self::$links_model ) {
				self::$links_model = \PLL()->model->get_links_model();
			}

			foreach ( $languages as $language ) {
				$transl_url = self::$links_model->switch_language_in_link( $data['url'], $language );
				if ( $transl_url === $data['url'] ) {
					continue;
				}

				echo '<url><loc>' . \esc_xml( \esc_url( $transl_url ) ) . '</loc>';

				if ( ! empty( $data['priority'] ) ) {
					echo '<priority>' . \esc_xml( $data['priority'] ) . '</priority>';
				}

				if ( ! empty( $data['lastmod'] ) ) {
					echo '<lastmod>' . \esc_xml( \get_date_from_gmt( $data['lastmod'], DATE_W3C ) ) . '</lastmod>';
				}

				echo '</url>';
			}
		}
	}

	/**
	 * Polylang sitemap subtype filter
	 *
	 * @param string $subtype The subtype.
	 *
	 * @return string
	 */
	public static function filter_sitemap_subtype( $subtype ) {
		return array_shift( explode( '-', $subtype ) );
	}
}
