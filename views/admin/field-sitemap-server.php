<?php
/**
 * Sitemap general settings server view
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_sitemap_server">
	<legend class="screen-reader-text">
		<?php echo esc_html( translate( 'Server' ) ); ?>
	</legend>

	<p>
		<label>
			<input type="radio" name="xmlsf_server" value="core"<?php disabled( $nosimplexml, true ); ?><?php checked( 'core' === $server && ! $nosimplexml, true ); ?> />
			<?php echo esc_html( translate( 'WordPress' ) ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="xmlsf_server" value="plugin"<?php checked( 'plugin' === $server || $nosimplexml, true ); ?> />
			<?php echo esc_html( translate( 'Plugin' ) ); ?>
		</label>
	</p>

	<?php
	if ( $nosimplexml ) {
		?>
	<p class="description">
		<?php
		printf( /* translators: Site Health admin page, linked */ esc_html__( 'It appears the SimpleXML module is not available. Please use the alternative XML sitemap server or install the missing PHP module. See recommendations on %s.', 'xml-sitemap-feed' ), '<a href="' . esc_url( admin_url( 'site-health.php' ) ) . '">' . esc_html( translate( 'Site Health' ) ) . '</a>' );
		?>
	</p>
		<?php
	}
	?>

</fieldset>
