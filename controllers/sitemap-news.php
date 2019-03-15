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
		add_action( 'transition_post_status', array($this,'do_pings'), 10, 3 );

		// FEED TEMPLATES
		add_action('do_feed_sitemap-news', 'xmlsf_news_load_template', 10, 1);
	}

	/**
	 * Do pings, hooked to transition post status
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function do_pings( $new_status, $old_status, $post ) {
		// are we publishing?
		if ( $old_status == 'publish' || $new_status != 'publish' )
			return;

		$ping = (array) get_option( 'xmlsf_ping', array() );

		if ( in_array( 'google', $ping ) ) {
			// check if we've got a post type that is included in our news sitemap
			// TODO also check category if needed
			$news_tags = get_option('xmlsf_news_tags');
			if ( ! empty( $news_tags['post_type'] ) && in_array( $post->post_type, (array) $news_tags['post_type'] ) ) {
				xmlsf_ping( 'google', $this->sitemap, 5 * MINUTE_IN_SECONDS );
			}
		}
	}

}

/**
 * set up the news sitemap template
 */
function xmlsf_news_load_template() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-news.php' );
}
