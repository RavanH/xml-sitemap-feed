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
			<input type="radio" name="xmlsf_server" value="core"<?php disabled( $nosimplexml || $nocoreserver, true ); ?><?php checked( 'core' === $server, true ); ?> />
			<?php echo esc_html( translate( 'WordPress' ) ); ?>
		</label>
		<br>
		<label>
			<input type="radio" name="xmlsf_server" value="plugin"<?php checked( 'plugin' === $server, true ); ?> />
			<?php echo esc_html( translate( 'Plugin' ) ); ?>
		</label>
	</p>

	<?php
	if ( $nosimplexml ) {
		?>
	<p class="description">
		<?php
		printf( /* translators: Site Health admin page, linked */ esc_html__( 'It appears the SimpleXML module is not available. Please use the Plugin\'s XML sitemap server or install the missing PHP module. See recommendations on %s.', 'xml-sitemap-feed' ), '<a href="' . esc_url( admin_url( 'site-health.php' ) ) . '">' . esc_html( translate( 'Site Health' ) ) . '</a>' );
		?>
	</p>
		<?php
	}
	if ( $nocoreserver ) {
		?>
	<p class="description">
		<?php
		printf( /* translators: WordPress Downloads page, linked */ esc_html__( 'It appears the WordPress core server is not available. Please use the Plugin\'s XML sitemap server or upgrade to %s.', 'xml-sitemap-feed' ), '<a href="https://wordpress.org/download/releases/" target="_blank">' . esc_html__( 'WordPress version 5.5+', 'xml-sitemap-feed' ) . '</a>' );
		?>
	</p>
		<?php
	}
	?>

</fieldset>
