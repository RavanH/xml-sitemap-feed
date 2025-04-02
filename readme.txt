=== XML Sitemap & Google News ===
Contributors: RavanH
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ravanhagen%40gmail%2ecom&item_name=XML%20Sitemap%20Feed
Tags: sitemap, xml, news, robots, Google News
Requires at least: 4.4
Requires PHP: 5.6
Tested up to: 6.8
Stable tag: 5.5.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

XML and Google News Sitemaps to feed the hungry spiders. Multisite, WP Super Cache, Polylang and WPML compatible.

== Description ==

This plugin creates dynamic feeds that comply with the **XML Sitemap** and the **Google News Sitemap** protocol. **Multisite**, **Polylang** and **WPML** compatible and there are no static files created.

There are options to control which sitemaps are enabled, which Post Types and archive pages (like taxonomy terms and author pages) are included, how Priority and Lastmod are calculated and a possibility to set additional robots.txt rules from within the WordPress admin.

You, or site owners on your Multisite network, will not be bothered with overly complicated settings like most other XML Sitemap plugins. The default settings will suffice in most cases.

An XML Sitemap Index becomes instantly available on **yourblog.url/sitemap.xml** (or yourblog.url/?feed=sitemap if you're not using a 'fancy' permalink structure) containing references to posts and pages by default, ready for indexing by search engines like Google, Bing, Yahoo, Yandex, Baidu, AOL and Ask. When the Google News Sitemap is activated, it will become available on **yourblog.url/sitemap-news.xml** (or yourblog.url/?feed=sitemap-news), ready for indexing by Google News. Both are automatically referenced in the dynamically created **robots.txt** on **yourblog.url/robots.txt** to tell search engines where to find your XML Sitemaps.

Please read the FAQ's for info on how to get your articles listed on Google News.

**Compatible with caching solutions** like CloudFlare, WP Super Cache, W3 Total Cache and Quick Cache that cache feeds, allowing a faster serving to the impatient (when hungry) spider.

**NOTES:**

1. If you _do not use fancy URL's_ or you have WordPress installed in a _subdirectory_, a dynamic **robots.txt will NOT be generated**. You'll have to create your own and upload it to your site root! See FAQ's.

2. On large sites, it is advised to use a good caching plugin like **WP Super Cache**, **Quick Cache**, **W3 Total Cache** or another to improve your site _and_ sitemap performance.

= Features =

* Compatible with multi-lingual sites using **Polylang** or **WPML** to allow all languages to be indexed equally.
* Option to add new robots.txt rules. These can be used to further control (read: limit) the indexation of various parts of your site and subsequent spread of pagerank across your sites pages.
* Includes XLS stylesheets for human readable sitemaps.
* Sitemap templates and stylesheets can be overridden by theme template files.

**XML Sitemap**

* Sitemap Index includes **posts**, **pages** and **authors** by default.
* Optionally include sitemaps for custom post types, categories and tags.
* Sitemap with custom URLs optional.
* Custom/static sitemaps can be added to the index.
* Works out-of-the-box, even on **Multisite** installations.
* Include featured images or attached images with title.
* Options to define which post types and taxonomies get included in the sitemap.
* Updates Lastmod on post modification or on comments.
* Set Priority per post type, per taxonomy and per individual post.
* Exclude individual posts and pages.

**Google News Sitemap**

* Required news sitemap tags: Publication name, language, title and publication date.
* Set a News Publication Name or uses site name.
* Supports custom post types.
* Limit inclusion to certain post categories.

= Pro Features =

**[Google News Advanced](https://premium.status301.com/downloads/google-news-advanced/)**

* Multiple post types - Include more than one post type in the same News Sitemap.
* Keywords - Add the keywords tag to your News Sitemap. Keywords can be created from Tags, Categories or a dedicated Keywords taxonomy.
* Stock tickers - Add stock tickers tag to your News Sitemap. A dedicated Stock Tickers taxonomy will be available to manage them.

= Privacy / GDPR =

This plugin does not collect any user or visitor data nor set browser cookies. Using this plugin should not impact your site privacy policy in any way.

**Data that is published**

An XML Sitemap index, referencing other sitemaps containing your web site's public post URLs of selected post types that are already public, along with their last modification date and associated image URLs, and any selected public archive URLs.

A Google News Sitemap containing your web site's public and recent (last 48 hours) URLs of selected news post type, along with their publication time stamp and associated image URL.
An author sitemap can be included, which will contain links to author archive pages. These urls contain author/user slugs, and the author archives can contain author bio information. If you wish to keep this out of public domain, then deactivate the author sitemap and use an SEO plugin to add noindex headers.

**Data that is transmitted**

No data actively transmitted.

= Contribute =

If you're happy with this plugin as it is, please consider writing a quick [rating](https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/#new-post) or helping other users out on the [support forum](https://wordpress.org/support/plugin/xml-sitemap-feed).

If you wish to help build this plugin, you're very welcome to [translate it into your language](https://translate.wordpress.org/projects/wp-plugins/xml-sitemap-feed/) or contribute code on [Github](https://github.com/RavanH/xml-sitemap-feed/).

= Credits =

XML Sitemap Feed was originally based on the discontinued plugin Standard XML Sitemap Generator by Patrick Chia. Since then, it has been completely rewritten and extended in many ways.


== Installation ==

= Wordpress =

**I.** If you have been using another XML Sitemap plugin before, check your site root and remove any created sitemap.xml, sitemap-news.xml and (if you're not managing this one manually) robots.txt files that remained there.

**II.** Install plugin by:

Quick installation via **[Covered Web Services](http://coveredwebservices.com/wp-plugin-install/?plugin=xml-sitemap-feed)** !

 &hellip; OR &hellip;

Search for "xml sitemap feed" and install with that slick **Plugins > Add New** admin page.

 &hellip; OR &hellip;

Follow these steps:

1. Download archive.

2. Upload the zip file via the Plugins > Add New > Upload page &hellip; OR &hellip; unpack and upload with your favourite FTP client to the /plugins/ folder.

**III.** Activate the plugin on the Plugins page.

Done! Check your sparkling new XML Sitemap by visiting yourblogurl.tld/sitemap.xml (adapted to your domain name of course) with a browser or any online XML Sitemap validator. You might also want to check if the sitemap is listed in your yourblogurl.tld/robots.txt file.

= WordPress 3+ in Multi Site mode =

Same as above but do a **Network Activate** to make a XML sitemap available for each site on your network.

Installed alongside [WordPress MU Sitewide Tags Pages](http://wordpress.org/plugins/wordpress-mu-sitewide-tags/), XML Sitemap Feed will **not** create a sitemap.xml nor change robots.txt for any **tag blogs**. This is done deliberately because they would be full of links outside the tags blogs own domain and subsequently ignored (or worse: penalised) by Google.

= Uninstallation =

Upon uninstalling the plugin from the Admin > Plugins page, plugin options and meta data will be cleared from the database. See notes in the uninstall.php file.

On multisite, the uninstall.php *can* loop through all sites in the network to perform the uninstalltion process for each site. However, this does not scale for large networks so it *only* does a per-site uninstallation when `define('XMLSF_MULTISITE_UNINSTALL', true);` is explicitly set in wp-config.php.


== Frequently Asked Questions ==

Please read more on [FAQ's and Troubleshooting](https://premium.status301.com/knowledge-base/xml-sitemap-google-news/faqs-and-troubleshooting/)

== Screenshots ==

1. XML Sitemap feed viewed in a normal browser. For your eyes only ;)
2. XML Sitemap source as read by search engines.


== Upgrade Notice ==

= 5.5.3 =
Bugfix release.

== Changelog ==

= 5.5.3 =
Date: 20250402
* FIX: missing exclude/priority meta box
* FIX: error blocking deactivation
* FIX: non-numeric value encountered in automatic priority calculations, thanks @i0n1ca
* FIX: undefined function on Clear settings

= 5.5 =
Date: 20250331
* NEW: Exclude option in Quick Editor
* NEW: Filter xmlsf_taxonomies_query_args
* NEW: Filter xmlsf_news_hours_old
* NEW: custom urls sitemap for core server
* NEW: external sitemaps for core server
* Namespacing & autoloading
* Admin notices for Slim SEO, Squirrly SEO, Jetpack Sitemaps & XML Sitemaps Manager
* FIX: Polylang news sitemap category selection
* FIX: Polylang user archive translations on Plugin sitemap
* FIX: Attempt to read property post_type on null
* FIX: Possible empty taxonomy sitemaps in index
* FIX: Missing textdomain, thanks @itapress
* FIX: Blog page lastmod date format
* FIX: Noindex robots meta header for core sitemap, thanks @ukheather
* FIX: possible empty static front page lastmod
* FIX: Max posts per sitemap not saving

= 5.4.9 =
Date: 20240506
* FIX: Unauthenticated file inclusion - CVE-2024-4441 reported by Foxyyy

= 5.4.8 =
Date: 20240329
* NEW: post types max number
* FIX: blog_public can be integer when object cache is used
* FIX: compatibility date redirect warning when using core server
* FIX: rewrite rules conflict with Polylang
* FIX: call to undefined function with Nginx Helper

= 5.4.5 =
Date: 20240221
* FIX: wp-cli disable plugin incompatibility
* FIX: trailing slash
* FIX: split by month
* FIX: disabled post types in index
* ClassicPress and WP pre-5.5 compatibility
* FIX: Undefined contact on uninstall
* FIX: admin compatibility message

= 5.4 =
Date: 20240219
* NEW: Switch between Plugin or WP core sitemap server for sitemap generation
* NEW: xmlsf_generator action hook
* NEW: xmlsf_sitemap_index_pre and xmlsf_sitemap_index_post action hooks
* NEW: xmlsf_author_has_published_posts filter
* Dropping all Ping Services (no longer supported)
* Dropping allowed domains filtering
* Exclude spammed or deleted authors on multisite
* Updated help links
* Update coding standards
* FIX: Don't use transients if not strictly needed
* FIX: "Failed opening required" when no template
* FIX: Conversion of false to array deprecated warning

= 5.3.6 =
Date: 20230810
* FIX: Work around get_users() fatal error in WP 6.3
* FIX: Wrong Nginx helper purge urls (backport from 5.4-beta)

= 5.3.5 =
Date: 20230629
* FIX: Forced Status 200 response conflict with Etag/If-None-Match headers, thanks @revolutionnaire

= 5.3.4 =
Date: 20230530
* FIX: File not found error on invalid sitemap requests
* FIX: Lastmod date older than post date on scheduled posts

= 5.3.3 =
Date: 20230528
* FIX: Undefined variable + Invalid argument supplied for foreach(), thanks @yankyaw09

= 5.3.2 =
* FIX: Bing ping 410 error response
* FIX: Outdated help & forum links

= 5.3.1 =
* FIX: Restore wp-sitemap.xml rewrite rules after deactivation
* FIX: Call to undefined function xmlsf_get_archives()
* Use nocache_headers()

= 5.3 =
* NEW: Author sitemap
* NEW: allow custom theme templates and stylesheets
* NEW: request filters `xmlsf_request` and `xmlsf_news_request`
* NEW: news template filters `xmlsf_news_publication_name` and `xmlsf_news_title`
* NEW: sitemap template action hook `xmlsf_url`
* NEW: sitemap template action hooks `xmlsf_news_url` and `xmlsf_news_tags_inner`
* NEW: `xmlsf_index_url_args` filter
* NEW: All in One SEO Pack incompatibility message and instructions
* NEW: The SEO Framework incompatibility message and instructions
* Moved news template action hook `xmlsf_news_tags_after` to after closing </news:news> tag
* Less DB queries, smaller memory footprint
* Better debug info with SAVEQUERIES
* Disable WP core sitemaps and redirect index
* FIX: conflicting static file deletion
* FIX: invalid form control not focusable when meta box is hidden
* FIX: force Status 200 response
* FIX: priority calculation last modified for post type
* FIX: news sitemap redirection with Polylang
* FIX: Cache-Control header no-cache

= 5.2.7 =
20191111
* NEW: Ad Inserter compatibility check
* NEW: xmlsf_urlset and xmlsf_news_urlset action hooks, thanks to Stanislav Khromov (@khromov)
* Exclude hierarchical post types from news sitemap

= 5.2.6 =
20191009
* NEW: xmlsf_tags_after, xmlsf_url_after and xmlsf_news_url_after action hooks
* Make stylesheet paths relative to prevent exclusion when using different language domains
* FIX: Taxonomy selection not available to new installs

= 5.2.4 =
20190917
* NEW Rank Math incompatibility admin warnings
* FIX undefined index
* FIX invalid form control

= 5.2.3 =
* FIX Cannot use return value in write context
* FIX issue #30 for sql_mode=ONLY_FULL_GROUP_BY, thanks @silvios
* FIX invalid form control not focusable when meta box is hidden

= 5.2.2 =
* FIX invalid date format on some PHP versions
* FIX Can't use function return value in write context
* FIX non-cyrillic URLs stripped from External Web Pages field
* FIX Call to undefined function xmlsf_cache_get_archives()

= 5.2 =
20190429
* Image query optimization and meta caching
* Last comment date meta caching
* Lastmod and publication dates in local time
* Removed ignored image tag from news sitemap
* Max memory limit for post type and taxonomy term sitemaps
* Prevent CDN file urls
* Zlib before GZhandler on .gz request
* FIX: don't ping for excluded posts
* FIX: traditional and simplified Chinese with WPML
* FIX: redundant front page url
* FIX: array_filter() expects parameter 1 to be array
* FIX: possible division by zero
* FIX: update_term_modified_meta
* FIX: rewrite rules on deactivate/uninstall

= 5.1.2 =
* FIX: admin notice dismiss button failing
* FIX: date archive redirect notice showing for inactive post types
* Plugin support and rate links on plugins page

= 5.1.1 =
* FIX options page not found
* FIX news sitemap only ping

= 5.1 =
20190313
* SEOPress and Catch Box incompatibility admin messages
* FIX bbPress incompatibility
* FIX failing last modified date for taxonomy sitemaps
* FIX sitemap showing when only private posts
* FIX possible sitemaps for no longer existing post types in index
* Admin interface improvements: highlighting and scroll
* Upgrade routines in own class only to be included when needed
* Moved metabox methods to dedicated classes
* NEW Respond to .gz requests (with ob_gzhandler output buffering if needed)
* NEW filters xmlsf_disabled_taxonomies, xmlsf_news_keywords, xmlsf_news_stock_tickers
* NEW action xmlsf_ping
* NEW Tools: Ping search engines and Flush rewrite rules

= 5.0.7 =
20181025
* Allowed domains back to Settings > Reading
* FIX static files check on activation
* NEW Admin warning on conflicting plugin settings
* FIX Empty post priority saved as 0.0
* FIX Call to undefined function xmlsf_get_archives()
* FIX force LC_NUMERIC locale to C
* FIX Call to private method
* FIX Custom post types with a hyphen not showing
* FIX Admin static files message fatal error, thanks @kitchin
* FIX Improper if statement in upgrade routine, thanks @kitchin
* FIX PHP 5.4 compatibility issues

= 5.0 =
20180908
* Complete code restructure and cleanup: MVC and JIT inclusion
* Fewer DB queries, much smaller memory footprint on normal queries
* NEW Admin interface with dedicated options pages and help tabs
* NEW Taxonomy term options: priority and automatic calculation
* NEW Admin warning on conflicting static files
* NEW Option to delete conflicting static files
* NEW Filters for post types: xmlsf_post_types and xmlsf_news_post_types
* NEW Filters for priority values: xmlsf_post_priority and xmlsf_term_priority
* Removal of Genre, Keywords and Access tags as Google dropped support
* Taxonomy term sitemaps speed improvement: get lastmod date from database
* Exclude Woocommerce product_shipping_class taxonomy
* PHP 7.2+ compat: create_function deprecated
* No more domain filtering for custom URLs and external sitemaps
* FIX Gutenberg editor GN genre taxonomy not showing
* FIX Plain and /index.php/ permalink structure
* FIX Clear all options on uninstall
* FIX Pings

= 4.9.4 =
* FIX: missing featured images, thanks @flyerua
* FIX: double content type response header filtering

= 4.9.3 =
* Reinstate filter_no_news_limits, allowing post type array
* Improved language handling and new language filter xmlsf_post_language
* Force text/xml Content-Type response header

= 4.9.2 =
* FactCheck genre causes error in Search Console

= 4.9.1 =
* FIX: double genre terms on upgrade from 4.4,  thanks @mouhalves
* FIX: wp_count_posts uncached and too heavy on large sites, thanks @carlalexander
* Last-modified response header now linked to Update on comments setting
* FIX: plugin_basename propagation

= 4.9 =
20180507
* Code cleanup
* NEW: FactCheck genre
* Changefreq tag dropped
* NEW: translation strings for genres
* FIX: zlib.output_compression compatibility, thanks @alicewondermiscreations
* FIX: permalink issue with Woocommerce account page endpoints
* FIX: undefined index in news post types

= 4.8.3 =
* fix get_lastpostdate array/string
* restore pre PHP 5.4 compatibility (popular request)

= 4.8 =
20180316
* NEW: Conditional functions is_sitemap() and is_news()
* code cleanup and annotation
* new google ping URL
* revisit get first/last date/modified functions and cache key set/delete
* FIX: cache key missing timezone
* FIX: wp_rewrite init before flush_rules

= 4.7.6 =
* FIX Open_BaseDir issue on IIS server

= 4.7.5 =
20161207
* FIX: On cache_flush purge also the respective time_key cache entry,
props @e2robert https://wordpress.org/support/topic/object-cache-issue-results-in-outdated-last-modified-values-on-index-sitemap/
* FIX: Variable variable php 7 compat
* Detect if headers are already sent and print warning in source

= 4.7.4 =
* Another WPML compat issue fixed, special thanks to hermes3por3

= 4.7.3 =
* NEW: xmlsf_excluded filter
* IMPROVEMENT: Polylang and WPML compatibility issues
* FIX: "Only variables should be passed by reference" strict warning
* FIX: PHP 5.3 compatibility

= 4.7 =
20160506
* WPML compatibility
* FIX: News Sitemap chinese language tag
* FIX: flush rules on plugin re-activation

= 4.6.3 =
* NEW: filter xmlsf_custom_sitemaps
* BUGFIX: empty custom urls sitemap

= 4.6.2 =
* NEW: filter xmlsf_custom_urls
* More cleanup
* BUGFIX: broken Polylang compatibility reported by @zesseb

= 4.6.1 =
20160407
* Code cleanup
* POT file update
* Dutch translation update

= 4.6 =
* NEW: xmlsf_news_tags_after action hook
* Attempt to remove static sitemap files left over by other sitemap plugins

= 4.5.1 =
* fix Persistent/Stored XSS vulnerability on admin page, thanks to Sneha Rajguru @Sneharajguru

= 4.5 =
* Set Google News access tag per post
* Exclude posts from Google News sitemap
* News Sitemap stylesheet text/links update
* FIX: cache_delete cached key instead of cache_flush as suggested by Jeremy Clarke https://wordpress.org/support/topic/please-stop-running-wp_cache_flush-whenever-posts-are-edited
* NEW: Nginx Helper compatibility to purge cache sitemap URLs from FastCGI Cache or Redis

= 4.4.1 =
* BUGFIX contribution by alejandra.aranibar: multiple news post types makes get_lastdate return oldest instead of newest date
* BUGFIX plugins_url filter not working, reported by Michael
* Dropped GN Geolocation tag support
* Dropped XMLSF_POST_TYPE and XMLSF_NEWS_POST_TYPE defines support
* Multiple default genres

= 4.4 =
* Pings max once per hour (5 minutes for news sitemap)
* Seperate ping for Google News Sitemap
* Append custom/static sitemaps to the index
* Include other post types in News Sitemap
* Optionally limit posts to certain categories in News Sitemap
* Noindex response header for sitemaps to keep them out of search results
* Static sitemap stylesheets
* Controversial default robots.txt rules removed
* DB query streamlining
* BUGFIX: fatal error on . (dot) as category base in permalinks
* BIGFIX: PHP Strict notices
* Force object cache flush on post publication

= 4.3.2 =
* Italian translation
* BUGFIX: html esc / filter image title and caption tags
* BUGFIX: empty terms counted causing empty taxonomy sitemap appearing in index
* BUGFIX: custom taxonomies where lastmod cannot be determined show empty lastmod tag

= 4.3 =
* Google News sitemap settings section
* Google News tags: access, genres, keywords, geo_locations
* Improved Google News stylesheet
* Custom Google News Publication Name
* Image tags in Google News sitemap
* Custom URLs
* Allow additional domains
* Image caption and title tags
* Ping Yandex and Baidu optional
* BUGFIX: Ineffective robots.txt rules
* BUGFIX: Priority value 0 in post meta not saved
* BUGFIX: Ping for all post types
* BUGFIX: Custom taxonomy support
* BUGFIX: Split by month shows year

= 4.2.4 =
* NEW: Image tags
* Rearranged settings section
* FIX: replace permalink, title and bloginfo rss filter hooks with own

= 4.2.3 =
* BUGFIX: Empty ping options after disabling the main sitemap
* BUGFIX: Empty language tag for Google News tags in posts sitemap
* Small back end changes
* NEW: Custom post types split by year/month

= 4.2 =
* NEW: Image & News tags
* NEW: Exclude pages/posts

= 4.1.4 =
* BUGFIX: Pass by reference fatal error in PHP 5.4
* BUGFIX: issue with Polylang language code in pretty permalinks setting
* BUGFIX: unselected post types in sitemap
* BUGFIX: 1+ Priority for sticky posts with comments
* Dutch and French translations updated

= 4.1 =
* NEW: Ping Google and Bing on new publications
* NEW: Set priority per post
* NEW: Priority calculation options
* NEW: Option to split posts by year or month for faster generation of each sitemap
* Reduced queries to increase performance
* Improved Lastmod and Changefreq calculations
* Core class improvements
* Dropped qTranslate support
* Dropped PHP4 support
* BUGFIX: removed several PHP notices

= 4.0.1 =
* NEW: Dutch and French translations
* BUGFIX: Non public sites still have sitemap by default
* BUGFIX: Invalid argument supplied for foreach() when all post types are off
* BUGFIX: Wrong translation dir

= 4.0.0 =
* Moved to sitemap index and seperated post/page sitemaps
* NEW: options to dswitch off sitemap and news sitemap
* NEW: select which post types to include
* NEW: select which taxonomies to include
* NEW: set additional robots.txt rules
* NEW: Translation POT catalogue
* Improved Polylang support
* Dropped xLanguage support
* qTranslate currently untested

= 3.9.2 =
* Basic Google News feed stylesheet
* improvement on XSS vulnerability fix
* Fixed trailing slash

= 3.9.1 =
* SECURITY: XSS vulnerability in sitemap.xsl.php

= 3.9 =
* Google News Sitemap
* Memory limit error workaround (for most sites)

= 3.8.8 =
* BUGFIX: PHP4 compatibility
* BUGFIX: stylesheet URL when installed in mu-plugins
* core change to class
* minified sitemap output by default

= 3.8.5 =
* **xLanguage support** based on code and testing by **Daniele Pelagatti**
* new FILTER HOOK `robotstxt_sitemap_url` for any translate and url changing plugins.
* BUGFIX: Decimal separator cannot be a comma!

= 3.8.3 =
* filter out external URLs inserted by plugins like Page Links To (thanks, Francois)
* minify sitemap and stylesheet output
* BUGFIX: qTranslate non-default language home URL

= 3.8 =
* **qTranslate support**
* no more Sitemap reference in robots.txt on non-public blogs

= 3.7.4 =
* switch from `add_feed` (on init) to the `do_feed_$feed` hook
* BUGFIX: `is_404()` condition TRUE and Response Header 404 on sites without posts
* BUGFIX: `is_feed()` condition FALSE after custom query_posts
* BUGFIX: no lastmod on home url when only pages on a site
* BUGFIX: stylesheet url wrong when WP installed in a subdir

= 3.7 =
* massive changefreq calculation improvement
* further priority calulation improvement taking last comment date into account

= 3.6.1 =
* BUGFIX: wrong date calculation on blogs less than 1 year old

= 3.6 =
* massive priority calculation improvement

= 3.5 =
* complete rewrite of plugin internals
* speed improvements
* WP 3.0 (normal and MS mode) ready

= 3.4 =
* BUGFIX: use home instead of siteurl for blog url for sitemap reference in robots.txt
* code streamline and cleanup

= 3.3 =
* automatic exclusion of tags blog in wpmu

= 3.2 =
* rewrite and add_feed calls improvements
* BUGFIX: double entry when static page is frontpage

= 3.0 =
* added styling to the xml feed to make it human readable

= 2.1 =
* BUGFIX: lastmod timezone offset displayed wrong (extra space and missing double-colon)

= 2.0 =
* priority calculation based on comments and age
* changefreq based on comments

= 1.0 =
* changed feed template location to avoid the need to relocate files outside the plugins folder
* BUGFIX: `get_post_modified_time` instead of `get_post_time`

= 0.1 =
* rework from Patrick Chia's [Standard XML Sitemaps](http://wordpress.org/plugins/standard-xml-sitemap/)
* increased post urls limit from 100 to 1000 (of max. 50,000 allowed by the Sitemap protocol)
