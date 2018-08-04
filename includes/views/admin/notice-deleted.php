<div class="notice notice-success fade is-dismissible">
    <p>
        <strong><?php _e('Successfully deleted:','xml-sitemap-feed') ?></strong>
    </p>
    <ul style="padding-left:20px;list-style:initial">
        <li><?php echo implode('</li><li>',$this->deleted) ?></li>
    </ul>
</div>
