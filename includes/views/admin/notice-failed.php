<div class="notice notice-error fade is-dismissible">
    <p>
        <strong><?php _e('Failed to delete:','xml-sitemap-feed'); ?></strong>
    </p>
    <ul style="padding-left:20px;list-style:initial">
        <li><?php echo implode('</li><li>',$this->failed); ?></li>
    </ul>
</div>
