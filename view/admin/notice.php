<div class="notice notice-warning fade is-dismissible">
	<p>
        <strong><?php _e('XML Sitemap & Google News Feeds','xml-sitemap-feed'); ?></strong>
    </p>
    <p>
        <?php printf( _n(
            'The following conflicting file has been found. Either delete it or disable the corresponding setting.',
            'The following %s conflicting files have been found. Either delete them or disable the corresponding settings.',
            $number,'xml-sitemap-feed'), number_format_i18n($number) ); ?>
    </p>
    <ul style="padding-left:20px;list-style:initial">
        <?php foreach ($this->static_files as $name => $file) { ?>
        <li>
            <strong><?php echo $name; ?></strong> (<?php echo $file; ?>) &nbsp;&ndash;&nbsp;
            <a style="color:red" href="?xmlsf-delete[]=<?php echo $name; ?>&amp;_wpnonce=<?php echo $nonce; ?>" onclick="return confirm('<?php
                printf(__('Attempt to delete %s.','xml-sitemap-feed'),$name); ?>\n\n<?php echo translate('Are you sure you want to do this?'); ?>')" />
                <?php echo translate('Delete'); ?>
            </a>
        </li>
        <?php } ?>
    </ul>
    <p>
        <a href="<?php echo admin_url('options-reading.php'); ?>#blog_public">
            <?php echo translate('Settings'); ?>
        </a> |
        <?php echo ( $number > 1 ) ? '<a style="color:red" href="?xmlsf-delete[]=' . implode( '&xmlsf-delete[]=', array_keys($this->static_files) ) . '&_wpnonce=' . $nonce . '" onclick="return confirm(\'' .
            __('Attempt to delete all conflicting files.','xml-sitemap-feed') . '\n\n' . translate('Are you sure you want to do this?') . '\')" />' .
            __('Delete all files','xml-sitemap-feed') . '</a> | ' : ''; ?>
        <a href="?xmlsf-static-dismiss&amp;_wpnonce=<?php echo $nonce; ?>">
            <?php echo translate('Dismiss'); ?>
        </a>
    </p>
</div>
