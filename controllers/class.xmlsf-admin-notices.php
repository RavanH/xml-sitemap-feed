<?php

/***
 * Dismissable or interactive admin notices
 */

class XMLSF_Admin_Notices extends XMLSF_Admin
{
	function __construct() {}

	public static function notice_static_files()
	{
		$number = count( parent::$static_files );
		if ( ! $number ) return;

		include XMLSF_DIR . '/views/admin/notice-static-files.php';
	}

	public static function notice_ad_inserter_feed()
	{
		include XMLSF_DIR . '/views/admin/notice-ad-insterter-feed.php';
	}

	public static function notice_catchbox_feed_redirect()
	{
		include XMLSF_DIR . '/views/admin/notice-catchbox-feed-redirect.php';
	}

	public static function notice_wpseo_date_redirect()
	{
		include XMLSF_DIR . '/views/admin/notice-wpseo-date-redirect.php';
	}

	public static function notice_wpseo_sitemap()
	{
		include XMLSF_DIR . '/views/admin/notice-wpseo-sitemap.php';
	}

	public static function notice_seopress_date_redirect()
	{
		include XMLSF_DIR . '/views/admin/notice-seopress-date-redirect.php';
	}

	public static function notice_seopress_sitemap()
	{
		include XMLSF_DIR . '/views/admin/notice-seopress-sitemap.php';
	}

	public static function notice_rankmath_date_redirect()
	{
		include XMLSF_DIR . '/views/admin/notice-rankmath-date-redirect.php';
	}

	public static function notice_rankmath_sitemap()
	{
		include XMLSF_DIR . '/views/admin/notice-rankmath-sitemap.php';
	}
}
