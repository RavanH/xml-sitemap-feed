<fieldset>
    <legend class="screen-reader-text"><?php echo translate('Categories'); ?></legend>
    <p>
        <?php _e('Limit to posts in these post categories:','xml-sitemap-feed'); ?>
    </p>
    <style type"text/css">
        ul.children { padding-left: 1em }
    </style>
    <ul class="cat-checklist">
		<?php echo $cat_list; ?>
	</ul>
    <p class="description">
         <?php _e('If you wish to limit posts that will feature in your News Sitemap to certain categories, select them here. If no categories are selected, posts of all categories will be included in your News Sitemap.','xml-sitemap-feed'); ?> 
    </p>

</fieldset>