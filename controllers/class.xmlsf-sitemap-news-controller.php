<?php
/* ------------------------------
 *      XMLSF Controller CLASS
 * ------------------------------ */

class XMLSF_Sitemap_News_Controller
{
	/**
	 * Sitemap index name
	 * @var string
	 */
	private $sitemap = 'sitemap-news.xml';

	/**
	 * Post types included in sitemap index
	 * @var array
	 */
	private $post_types;

	/**
	 * CONSTRUCTOR
	 * Runs on init
	 */

	function __construct( $sitemap )
	{
		$this->sitemap = $sitemap;

		// PINGING
		add_action( 'transition_post_status', array($this,'do_ping'), 11, 3 ); // must run after post meta data hanling hooked at proirity 9

		// FEED TEMPLATES
		add_action( 'do_feed_sitemap-news', 'xmlsf_news_load_template', 10, 1 );
	}

	/**
	 * Do pings, hooked to transition post status
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_ping( $new_status, $old_status, $post )
	{
		// bail out when...
		if (
			// already published or not publishing
			$old_status == 'publish' || $new_status != 'publish' ||
			// REST API call without new post data, see Gutenberg issue https://github.com/WordPress/gutenberg/issues/15094
			empty( $_POST ) || ! empty( $_POST['_xmlsf_news_exclude'] ) ||
			// google ping not activated
			! in_array( 'google', (array) get_option( 'xmlsf_ping' ) )
		) return;

		$news_tags = (array) get_option('xmlsf_news_tags');

		// is this an active post type?
		if ( empty( $news_tags['post_type'] ) || ! in_array( $post->post_type, (array) $news_tags['post_type'] ) ) return;

		// are categories limited and is not in correct category?
		if ( ! empty( $news_tags['categories'] ) ) {
			$cats = wp_get_post_categories( $post->ID, array( 'fields' => 'ids' ) );
			if ( empty( array_intersect( (array) $cats, (array) $news_tags['categories'] ) ) ) return;
		}

		// PING
		xmlsf_ping( 'google', $this->sitemap, 5 * MINUTE_IN_SECONDS );
	}

}

/**
 * set up the news sitemap template
 */
function xmlsf_news_load_template() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-news.php' );
}
