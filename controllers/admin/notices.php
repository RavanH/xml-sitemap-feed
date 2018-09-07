<?php

class XMLSF_Admin_Notices extends XMLSF_Admin_Controller {

	function __construct() {}

	public static function wpseo_date_redirect_admin_notice() {
		//$screen = get_current_screen();
		if ( !get_user_meta( get_current_user_id(), 'xmlsf_date_redirect_error_dismissed' ) /*$screen->id === 'options-reading' */) {
			$nonce = wp_create_nonce( XMLSF_BASENAME.'-notice' );

			include XMLSF_DIR . '/views/admin/notice-date-redirect.php';
		}
	}

	public static function clear_settings_admin_notice() {
		include XMLSF_DIR . '/views/admin/notice-cleared.php';
	}

	public static function static_files_admin_notice_dismiss() {
		if ( isset( $_POST['_xmlsf_notice_nonce'] ) && wp_verify_nonce( $_POST['_xmlsf_notice_nonce'], XMLSF_BASENAME.'-notice' ) ) {
			add_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed', 'true', true );
		} else {
			add_action( 'admin_notices', array(self,'admin_notice_nonce_fail') );
		}
	}

	public static function date_redirect_admin_notice_dismiss() {
		if ( isset( $_POST['_xmlsf_notice_nonce'] ) && wp_verify_nonce( $_POST['_xmlsf_notice_nonce'], XMLSF_BASENAME.'-notice' ) ) {
			add_user_meta( get_current_user_id(), 'xmlsf_date_redirect_error_dismissed', 'true', true );
		} else {
			add_action( 'admin_notices', array(self,'admin_notice_nonce_fail') );
		}
	}

	public static function static_files_admin_notice_deleted() {
		include XMLSF_DIR . '/views/admin/notice-deleted.php';
	}

	public static function static_files_admin_notice_not_allowed() {
		include XMLSF_DIR . '/views/admin/notice-not-allowed.php';
	}

	public static function static_files_admin_notice_none_found() {
		include XMLSF_DIR . '/views/admin/notice-none-found.php';
	}

	public static function static_files_admin_notice_failed() {
		include XMLSF_DIR . '/views/admin/notice-failed.php';
	}

	public static function admin_notice_nonce_fail() {
		include XMLSF_DIR . '/views/admin/notice-nonce-error.php';
	}

	public static function static_files_admin_notice() {
		//$screen = get_current_screen();
		if ( !get_user_meta( get_current_user_id(), 'xmlsf_static_files_warning_dismissed' ) /*$screen->id === 'options-reading' */) {
			$nonce = wp_create_nonce( 'xmlsf-static-warning-nonce' );

			if ( $number = count(parent::$static_files) )
				include XMLSF_DIR . '/views/admin/notice.php';
		}
	}

}
