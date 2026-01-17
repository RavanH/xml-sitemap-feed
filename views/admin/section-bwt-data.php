<?php
/**
 * BWT data section
 *
 * @package XML Sitemap & Google News
 */

// Get connect data.
$options = (array) get_option( 'xmlsf_bwt_connect', array() );
if ( empty( $options['bing_api_key'] ) ) {
	// Initiate button.
	?>
	<p>
		<?php printf( /* translators: %s: Bing Webmaster Tools */ esc_html_x( 'Connect to %s for sitemap data retrieval and sitemap submissions.', 'Bing Webmaster Tools connection', 'xml-sitemap-feed' ), esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( XMLSF\Admin\BWT_Connect::get_settings_url() ); ?>" class="button button-primary">
			<?php esc_html_e( 'Connect', 'xml-sitemap-feed' ); ?>
		</a>
	</p>
	<?php
	return;
}

$sitemap = xmlsf()->sitemap->get_sitemap_url();
$data    = XMLSF\BWT_Connect::get( $sitemap );

?>
<p><?php esc_html_e( 'Your sitemap data as reported by Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?></p>
<?php
if ( \is_wp_error( $data ) ) {
	// Display error message.
	?>
	<p style="color:#d63638">
		<?php esc_html_e( 'There was an error requesting sitemap data from Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?>
		<br>
		<?php echo esc_html( $data->get_error_message() ); ?>
	</p>
	<p>
		<a href="" class="button button-primary"><?php echo esc_html( translate( 'Retry' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></a>
	</p>
	<?php
	return;
}
$number = count( $data['d'] );
if ( $number < 1 ) {
	?>
	<p style="color:#d63638">
		<?php esc_html_e( 'There was an error requesting sitemap data from Bing Webmaster Tools.', 'xml-sitemap-feed' ); ?>
		<br>
		<?php printf( /* translators: %s: Bing Webmaster Tools */ esc_html__( 'Your sitemap was not found on %s. Maybe submit it first?', 'xml-sitemap-feed' ), esc_html__( 'Bing Webmaster Tools', 'xml-sitemap-feed' ) ); ?>
	</p>
	<?php
	return;
}

$data            = $data['d'][0];
$format          = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
$last_submitted  = isset( $data['Submitted'] ) ? wp_date( $format, substr( $data['Submitted'], 6, 10 ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$last_downloaded = isset( $data['LastCrawled'] ) ? wp_date( $format, substr( $data['LastCrawled'], 6, 10 ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$links_submitted = isset( $data['UrlCount'] ) ? $data['UrlCount'] : 0;
$bwt_link        = add_query_arg(
	array(
		'siteUrl'      => rawurlencode( \home_url() ),
		'sitemapIndex' => rawurlencode( $sitemap ),
	),
	'https://www.bing.com/webmasters/sitemaps'
);
// https://www.bing.com/webmasters/sitemaps?siteUrl=https%3A%2F%2Fdev.status301.com%2F&sitemapIndex=https%3A%2F%2Fdev.status301.com%2Fwp-sitemap.xml&activePivot=1 .
?>
<table class="widefat">
	<thead>
		<tr>
			<th><?php esc_html_e( 'XML Sitemap Index', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Status', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last submitted', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last crawled', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'URLs', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Sitemaps', 'xml-sitemap-feed' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>
				<a href="<?php echo esc_url( $bwt_link ); ?>" target="_blank" title="<?php esc_html_e( 'View this sitemap in Bing Webmaster Tools', 'xml-sitemap-feed' ); ?>">
					<?php echo esc_html( $sitemap ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</th>
			<td><?php if ( isset( $data['Status'] ) && 'Success' !== $data['Status'] ) : ?>
				<span class="dashicons dashicons-clock" style="color:#dba617" title="<?php esc_html_e( 'Pending', 'xml-sitemap-feed' ); ?>"></span>
			<?php else : ?>
				<span class="dashicons dashicons-yes-alt" style="color:#00a32a" title="<?php echo esc_html_e( 'Processed', 'xml-sitemap-feed' ); ?>"></span>
			<?php endif; ?>
			</td>
			<td><?php echo esc_html( $last_submitted ); ?></td>
			<td><?php echo esc_html( $last_downloaded ); ?></td>
			<td><?php echo esc_html__( 'Found:', 'xml-sitemap-feed' ) . ' ' . esc_html( $links_submitted ); ?></td>
			<td><?php echo esc_html__( 'Found:', 'xml-sitemap-feed' ) . ' ' . esc_html( $number ); ?></td>
		</tr>
	</tbody>
</table>
