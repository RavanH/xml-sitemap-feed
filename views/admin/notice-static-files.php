<?php
/**
 * Admin notice: Static files
 *
 * @package XML Sitemap & Google News
 */

?>
<div class="notice notice-warning fade is-dismissible">
	<p>
		<strong><?php esc_html_e( 'XML Sitemap & Google News', 'xml-sitemap-feed' ); ?></strong>
	</p>
	<p>
	<?php
	$number = count( self::$static_files );
	printf(
		esc_html(
			/* translators: %s is Reading Settings URL */
			_n(
				'The following static file has been found. Either delete it or disable the conflicting <a href="%s">sitemap</a>.',
				'The following static files have been found. Either delete them or disable the conflicting <a href="%s">sitemaps</a>.',
				$number,
				'xml-sitemap-feed'
			)
		),
		esc_url( admin_url( 'options-reading.php' ) ) . '#xmlsf_sitemaps'
	);
	?>
	</p>
	<form action="" method="post">
		<?php wp_nonce_field( XMLSF_BASENAME . '-notice', '_xmlsf_notice_nonce' ); ?>
		<ul>
			<?php foreach ( self::$static_files as $name => $file ) { ?>
			<li>
				<label><input type="checkbox" name="xmlsf-delete[]" value="<?php echo esc_attr( $name ); ?>" /> <strong><?php echo esc_html( $name ); ?></strong> (<?php echo esc_html( $file ); ?>)</label>
			</li>
			<?php } ?>
		</ul>
		<p>
			<input type="submit" class="button button-small" name="xmlsf-delete-submit" value="<?php esc_html_e( 'Delete selected files', 'xml-sitemap-feed' ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Attempt to delete selected conflicting files.', 'xml-sitemap-feed' ) ); ?>\n\n<?php echo esc_js( translate( 'Are you sure you want to do this?' ) ); ?>')" />
			&nbsp;
			<input type="hidden" name="xmlsf-dismiss" value="static_files" />
			<input type="submit" class="button button-small" name="xmlsf-dismiss-submit" value="<?php echo esc_attr( translate( 'Dismiss' ) ); ?>" />
		</p>
	</form>
</div>
