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
	 * Get Polylang translations
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	public function get_translations( $post_id ) {
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
	public function filter_request( $request ) {
		$request['lang'] = '';

		return $request;
	}

	/**
	 * Polylang compatibility actions
	 *
	 * @param array $request The request.
	 *
	 * @return array
	 */
	public function request_actions( $request ) {
		global $polylang;

		// Prevent language redirections.
		\add_filter( 'pll_check_canonical_url', '__return_false' );

		if ( isset( $polylang ) ) {
			// Remove Polylang filters to place all languages in the same sitemaps.
			\remove_filter( 'pll_set_language_from_query', array( $polylang->sitemaps, 'set_language_from_query' ) );
			\remove_filter( 'rewrite_rules_array', array( $polylang->sitemaps, 'rewrite_rules' ) );
			\remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
		}

		return $request;
	}

	/**
	 * News publication name filter for Polylang.
	 *
	 * @param string $name Publication name.
	 * @param int    $post_id Post ID.
	 *
	 * @return string
	 */
	public function news_name( $name, $post_id ) {
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
	public function post_language_filter( $locale, $post_id ) {
		return \function_exists( 'pll_get_post_language' ) ? \pll_get_post_language( $post_id, 'locale' ) : $locale;
	}

	/**
	 * Pre register news provider action.
	 */
	public function pre_register_news_provider() {
		// Polylang compatibility: prevent sitemap translations.
		global $polylang;
		if ( isset( $polylang ) && \is_object( $polylang->sitemaps ) ) {
			\remove_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) );
		}
	}

	/**
	 * Post register news provider action.
	 */
	public function post_register_news_provider() {
		global $polylang;
		if ( isset( $polylang ) && ! \has_filter( 'wp_sitemaps_add_provider', array( $polylang->sitemaps, 'replace_provider' ) ) ) {
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
	public function root_data( $data ) {
		if ( \function_exists( 'pll_languages_list' ) && \function_exists( 'pll_home_url' ) ) {
			$languages = \pll_languages_list();
			if ( is_array( $languages ) ) {
				foreach ( $languages as $language ) {
					$url          = \pll_home_url( $language );
					$data[ $url ] = array(
						'priority' => '1.0',
						'lastmod'  => \get_date_from_gmt( \get_lastpostdate( 'gmt', 'post' ), DATE_W3C ),
						// TODO make lastmod date language specific.
					);
				}
			}
		}

		return $data;
	}
}
