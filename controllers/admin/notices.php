<?php

/***
 * Dismissable or interactive admin notices
 */

class XMLSF_Admin_Notices extends XMLSF_Admin_Controller
{
	function __construct() {}

	public static function notice_static_files()
	{
		$number = count( parent::$static_files );
		if ( 0 == $number ) return;
		
		$static_files = parent::$static_files;

		include XMLSF_DIR . '/views/admin/notice-static-files.php';
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
}
