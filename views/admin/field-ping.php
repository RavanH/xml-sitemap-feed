<fieldset id="xmlsf_ping">
    <legend class="screen-reader-text"><?php echo __('Ping Services','xml-sitemap-feed'); ?></legend>

	<label>
        <input type="checkbox" name="xmlsf_ping[]" id="xmlsf_ping_google" value="google"<?php echo checked( is_array($options) && in_array('google',$options), true, false); ?> />
		<?php _e('Google','xml-sitemap-feed'); ?>
	</label>

    <br>

	<label>
        <input type="checkbox" name="xmlsf_ping[]" id="xmlsf_ping_bing" value="bing"<?php echo checked( is_array($options) && in_array('bing',$options), true, false); ?> />
		<?php _e('Bing & Yahoo','xml-sitemap-feed'); ?>
	</label>
</fieldset>
<script>
jQuery( 'document' ).ready( function( $ ) {
	if ( window.location.hash === '#xmlsf_ping' ) {
		$( '#xmlsf_ping' ).closest( 'td' ).addClass( 'highlight' );
		$( 'html, body' ).animate( { scrollTop: $("#xmlsf_ping").offset().top-40 }, 800 );
	}
} );
</script>
