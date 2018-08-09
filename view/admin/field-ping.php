<fieldset id="xmlsf_ping">
    <legend class="screen-reader-text"><?php echo __('Ping Services','xml-sitemap-feed'); ?></legend>

	<label>
        <input type="checkbox" name="xmlsf_ping[google][active]" id="xmlsf_ping_google" value="1"<?php echo checked( !empty($options['google']['active']), true, false); ?> />
		<?php _e('Google','xml-sitemap-feed'); ?>
	</label>

	<?php if (isset($pong['google'])) : ?>
	<span class="description">
	<?php
	foreach ((array)$pong['google'] as $pretty => $data) {
		if ( !empty($data['time']) ) {
			if ( '200' == $data['code'] ) {
			?>
		&nbsp;&ndash;&nbsp; <?php printf(__('Successfully sent %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time'])); ?>
			<?php
			} else {
			?>
		&nbsp;&ndash;&nbsp; <?php sprintf(__('Failed to send %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time'])); ?>
			<?php
			}
		}
	}
	?>
	</span>
	<?php endif; ?>

    <br>

	<label>
        <input type="checkbox" name="xmlsf_ping[bing][active]" id="xmlsf_ping_bing" value="1"<?php echo checked( !empty($options['bing']['active']), true, false); ?> />
		<?php _e('Bing & Yahoo','xml-sitemap-feed'); ?>
	</label>

	<?php if (isset($pong['google'])) : ?>
	<span class="description">
	<?php
	foreach ((array)$pong['bing'] as $pretty => $data) {
		if ( !empty($data['time']) ) {
			if ( '200' == $data['code'] ) {
			?>
		&nbsp;&ndash;&nbsp; <?php printf(__('Successfully sent %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time'])); ?>
			<?php
			} else {
			?>
		&nbsp;&ndash;&nbsp; <?php sprintf(__('Failed to send %1$s on %2$s.','xml-sitemap-feed'),$pretty, date($timezone_format,$data['time'])); ?>
			<?php
			}
		}
	}
	?>
	</span>
<?php endif; ?>

</fieldset>
