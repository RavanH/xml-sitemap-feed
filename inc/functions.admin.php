<?php

// plugin action links

function xmlsf_add_action_link( $links ) {
	$settings_link = '<a href="' . admin_url('options-reading.php') . '#xmlsf_sitemaps">' . translate('Settings') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

function xmlsf_plugin_meta_links( $links, $file ) {
	if ( $file == XMLSF_BASENAME ) {
		$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/">' . __('Support','xml-sitemap-feed') . '</a>';
		$links[] = '<a target="_blank" href="https://wordpress.org/support/plugin/xml-sitemap-feed/reviews/?filter=5#new-post">' . __('Rate ★★★★★','xml-sitemap-feed') . '</a>';
	}
	return $links;
}

// verification

function xmlsf_verify_nonce( $context ) {

	if ( isset( $_POST['_xmlsf_'.$context.'_nonce'] ) && wp_verify_nonce( $_POST['_xmlsf_'.$context.'_nonce'], XMLSF_BASENAME.'-'.$context ) )
		return true;

	// Still here? Then add security check failed error message and return false.
	add_settings_error( 'security_check_failed', 'security_check_failed', translate('Security check failed.') /* . ' Context: '. $context */ );

	return false;
}
