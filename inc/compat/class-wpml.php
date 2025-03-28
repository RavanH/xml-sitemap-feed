<?php
/**
 * WPML compatibility
 *
 * @package XML Sitemap & Google News
 */

namespace XMLSF\Compat;

/**
 * WPML compatibility class
 */
class WPML {
	/**
	 * Get WPML translations
	 *
	 * @param int $post_id Post id.
	 *
	 * @return array
	 */
	public static function get_translations( $post_id ) {

		global $sitepress;
		$translation_ids = array();

		if ( \is_object( $sitepress ) && \method_exists( $sitepress, 'get_languages' ) && \method_exists( $sitepress, 'get_object_id' ) ) {

			foreach ( \array_keys( $sitepress->get_languages( false, true ) ) as $term ) {
				$id = $sitepress->get_object_id( $post_id, 'page', false, $term );
				if ( $post_id !== $id ) {
					$translation_ids[] = $id;
				}
			}
		}

		return $translation_ids;
	}

	/**
	 * WPML compatibility hooked into add_settings and news_add_settings actions
	 *
	 * @return void
	 */
	public static function remove_home_url_filter() {
		// Remove WPML home url filter.
		global $wpml_url_filters;
		if ( \is_object( $wpml_url_filters ) ) {
			\remove_filter( 'home_url', array( $wpml_url_filters, 'home_url_filter' ), - 10 );
		}
	}

	/**
	 * WPML compatibility hooked into xml request filter
	 *
	 * @param array $request The request.
	 *
	 * @return array
	 */
	public static function filter_request( $request ) {
		$request['lang'] = ''; // Strip off potential lang url parameter.

		return $request;
	}

	/**
	 * WPML compatibility hooked into xml request filter
	 *
	 * @return void
	 */
	public static function request_actions() {
		global $sitepress, $wpml_query_filter;

		if ( \is_object( $sitepress ) ) {
			// Remove filters for tax queries.
			\remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
			\remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
			\remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
			// Set language to all.
			$sitepress->switch_lang( 'all' );
		}

		if ( $wpml_query_filter ) {
			// Remove query filters.
			\remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
			\remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
		}
	}

	/**
	 * WPML: switch language
	 *
	 * @see https://wpml.org/wpml-hook/wpml_post_language_details/
	 */
	public static function language_switcher() {
		global $sitepress, $post;

		if ( \is_object( $sitepress ) ) {
			$language = \apply_filters(
				'wpml_element_language_code',
				null,
				array(
					'element_id'   => $post->ID,
					'element_type' => $post->post_type,
				)
			);
			$sitepress->switch_lang( $language );
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
		global $sitepress;

		if ( \is_object( $sitepress ) && \method_exists( $sitepress, 'get_languages' ) && \method_exists( $sitepress, 'language_url' ) ) {
			$data = array();

			foreach ( \array_keys( $sitepress->get_languages( false, true ) ) as $term ) {
				$url          = $sitepress->language_url( $term );
				$data[ $url ] = array(
					'lastmod' => \get_lastpostdate( 'gmt', 'post' ), // TODO make lastmod date language specific.
				);
			}
		}

		return $data;
	}
}
