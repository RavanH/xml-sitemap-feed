<fieldset>
  <legend class="screen-reader-text">
    <?php echo translate('Categories'); ?>
  </legend>
  <p>
    <?php _e('Limit to posts in these post categories:','xml-sitemap-feed'); ?>
  </p>
  <style type"text/css">ul.children{padding-left:1em}</style>
  <ul class="cat-checklist">
		<?php echo $cat_list; ?>
	</ul>
</fieldset>
