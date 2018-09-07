<?php
/**
 * set up the news sitemap template
 */
function xmlsf_load_template_news() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-news.php' );
}

add_action('do_feed_sitemap-news', 'xmlsf_load_template_news', 10, 1);
