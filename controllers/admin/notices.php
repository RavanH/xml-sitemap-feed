<?php

class XMLSF_Admin_Notices extends XMLSF_Admin_Controller
{
	function __construct() {}

	public static function notice_clear_settings()
	{
		include XMLSF_DIR . '/views/admin/notice-cleared.php';
	}

	public static function static_files_deleted()
	{
		include XMLSF_DIR . '/views/admin/notice-deleted.php';
	}

	public static function static_files_not_allowed()
	{
		include XMLSF_DIR . '/views/admin/notice-not-allowed.php';
	}

	public static function static_files_none_found()
	{
		include XMLSF_DIR . '/views/admin/notice-none-found.php';
	}

	public static function static_files_failed()
	{
		include XMLSF_DIR . '/views/admin/notice-failed.php';
	}

	public static function notice_nonce_fail()
	{
		include XMLSF_DIR . '/views/admin/notice-nonce-error.php';
	}

	public static function notice_static_files()
	{
		if ( !in_array( 'static_files', get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ) ) ) {
			if ( $number = count(parent::$static_files) )
				include XMLSF_DIR . '/views/admin/notice-static-files.php';
		}
	}

	public static function notice_wpseo_date_redirect()
	{
		if ( !in_array( 'date_redirect', get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ) ) ) {
			include XMLSF_DIR . '/views/admin/notice-wpseo-date-redirect.php';
		}
	}

	public static function notice_wpseo_sitemap()
	{
		if ( !in_array( 'wpseo_sitemap', get_user_meta( get_current_user_id(), 'xmlsf_dismissed' ) ) ) {
			include XMLSF_DIR . '/views/admin/notice-wpseo-sitemap.php';
		}
	}
}
