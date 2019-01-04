<?php
/**
 * set up the news sitemap template
 */
function xmlsf_news_load_template() {
	load_template( XMLSF_DIR . '/views/feed-sitemap-news.php' );
}

add_action('do_feed_sitemap-news', 'xmlsf_news_load_template', 10, 1);
