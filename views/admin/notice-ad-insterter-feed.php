<div class="notice notice-error fade is-dismissible">
  <p>
    <?php printf( /* Translators: RSS Feed (plugin option name), Plugin name, plugin settings page (linked) */
		__( 'The option %1$s in %2$s is not compatible with %3$s. Please disable it under the %4$s tab of each active ad block.', 'xml-sitemap-feed'),
			'<strong>' . translate('RSS Feed', 'ad-inserter') . '</strong>',
			'<a href="' . admin_url('options-general.php') . '?page=ad-inserter.php">' . translate('Ad Inserter', 'ad-inserter') . '</a>',
			__('XML Sitemap & Google News','xml-sitemap-feed'),
			translate('Misc', 'ad-inserter')
		); ?>
  </p>
</div>
