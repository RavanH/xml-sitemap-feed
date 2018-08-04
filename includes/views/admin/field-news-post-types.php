<fieldset>
    <legend class="screen-reader-text"><?php _e('Include post types','xml-sitemap-feed'); ?></legend>
        <?php echo implode('<br/>
        ',$options); ?>
    <p class="description"><?php printf(__('At least one post type must be selected. By default, the post type %s will be used.','xml-sitemap-feed'),translate('Posts')); ?></p>
</fieldset>
