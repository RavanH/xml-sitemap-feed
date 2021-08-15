<div class="notice notice-error fade is-dismissible">
  <p>
    <?php printf( /* Translators: Feed Redirect URL (Theme option name), Plugn name, Theme Options, Customizer (linked to Customizer page) */
		__( 'The Catch Box theme option %1$s is not compatible with %2$s. Please go to %3$s in the %4$s and remove it.', 'xml-sitemap-feed'),
			'<strong>' . translate('Feed Redirect URL', 'catch-box') . '</strong>',
			__('XML Sitemap & Google News','xml-sitemap-feed'),
			'<strong>' . translate('Theme Options', 'catch-box') . '</strong>',
			'<a href="' . admin_url('customize.php') . '" target="_blank">' . translate('Customizer') . '</a>'
		); ?>
  </p>
</div>
