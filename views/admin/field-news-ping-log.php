<fieldset>
    <legend class="screen-reader-text">
		<?php _e('Ping log','xml-sitemap-feed'); ?>
	</legend>
  <p>
  	<label>
  		<input type="checkbox" id="xmlsf_news_ping_log" value="1" disabled="disabled" />
  		<?php _e('Enable Google News ping log', 'xml-sitemap-feed'); ?>
  	</label>
  </p>
  <p>
    <label>
      <?php _e('Maximum log entries','xml-sitemap-feed'); ?>
      <input type="number" step="1" min="0" max="10000" id="xmlsf_news_ping_log_max" value="1000" class="medium-text" disabled="disabled" />
    </label>
  </p>
	<p class="description">
		<?php _e('Keep a log of all News Sitemap pings to Google and their responses. The log entries are stored in the database so keep the maximum number as low as is useful for you. Disabling the ping log will clear all log entries from the database.','xml-sitemap-feed'); ?>
	</p>
</fieldset>
