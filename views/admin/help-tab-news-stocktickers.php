<?php
/**
 * Help tab: News stocktickers
 *
 * @package XML Sitemap & Google News
 */

?>
<p>
	<?php esc_html_e( 'Enabling stock tickers will create a custom taxonomy, allowing to attach stocks to individual posts.', 'xml-sitemap-feed' ); ?>
	<?php esc_html_e( 'Each ticker must be prefixed by the name of its stock exchange, and must match its entry in Google Finance. For example: NASDAQ:AMAT (but not NASD:AMAT) or BOM:500325 (but not BOM:RIL).', 'xml-sitemap-feed' ); ?>
</p>
<p>
	<?php esc_html_e( 'Note: Google News allows at most 5 stocks per article in the News Sitemap. The stocks must be relevant to the related news article.', 'xml-sitemap-feed' ); ?>
</p>
