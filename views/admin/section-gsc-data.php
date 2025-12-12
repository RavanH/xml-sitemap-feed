<?php
/**
 * GSC data section
 *
 * @package XML Sitemap & Google News
 */

// Get connect data.
$options = (array) get_option( 'xmlsf_gsc_connect', array() );
if ( empty( $options['google_refresh_token'] ) ) {
	// Initiate button.
	?>
	<p>
		<?php printf( /* translators: %s: Google Search Console */ esc_html_x( 'Connect to %s for sitemap data retrieval and sitemap submissions.', 'Google Search Console connection', 'xml-sitemap-feed' ), esc_html__( 'Google Search Console', 'xml-sitemap-feed' ) ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( add_query_arg( 'ref', 'xmlsf', XMLSF\Admin\GSC_Connect::get_settings_url() ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Connect', 'xml-sitemap-feed' ); ?>
		</a>
	</p>
	<?php
	return;
}

$sitemap = xmlsf()->sitemap->get_sitemap_url();
$data    = XMLSF\GSC_Connect::get( $sitemap );

?>
<p><?php esc_html_e( 'Your sitemap data as reported by Google Search Console.', 'xml-sitemap-feed' ); ?></p>
<?php
if ( \is_wp_error( $data ) ) {
	// Display error message.
	?>
	<p style="color:#d63638">
		<?php esc_html_e( 'There was an error requesting sitemap data from Google Search Console.', 'xml-sitemap-feed' ); ?>
		<br>
		<?php echo esc_html( $data->get_error_message() ); ?>
	</p>
	<p>
		<a href="" class="button button-primary"><?php echo esc_html( translate( 'Retry' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></a>
	</p>
	<?php

	return;
}

$format          = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
$last_submitted  = isset( $data['lastSubmitted'] ) ? wp_date( $format, strtotime( $data['lastSubmitted'] ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$is_pending      = isset( $data['isPending'] ) ? $data['isPending'] : false;
$last_downloaded = isset( $data['lastDownloaded'] ) ? wp_date( $format, strtotime( $data['lastDownloaded'] ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$_warnings       = isset( $data['warnings'] ) ? $data['warnings'] : 0;
$_errors         = isset( $data['errors'] ) ? $data['errors'] : 0;
$property        = XMLSF\Admin\GSC_Connect::get_property_url();
$gsc_link        = add_query_arg(
	array(
		'resource_id'   => rawurlencode( $property ),
		'sitemap_index' => rawurlencode( $data['path'] ),
	),
	'https://search.google.com/search-console/sitemaps/sitemap-index-drilldown'
);
$links_submitted = 0;
$links_indexed   = 0;
if ( isset( $data['contents'] ) && is_array( $data['contents'] ) ) {
	foreach ( $data['contents'] as $content ) {
		if ( isset( $content['type'] ) && 'web' === $content['type'] ) {
			$links_submitted = $content['submitted'];
			$links_indexed   = $content['indexed'];
			break;
		}
	}
}

?>
<table class="widefat">
	<thead>
		<tr>
			<th><?php esc_html_e( 'XML Sitemap Index', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Status', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last submitted', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last crawled', 'xml-sitemap-feedcomplet' ); ?></th>
			<th><?php esc_html_e( 'URLs', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Issues', 'xml-sitemap-feed' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>
				<a href="<?php echo esc_url( $gsc_link ); ?>" target="_blank" title="<?php esc_html_e( 'View this sitemap in Google Search Console', 'xml-sitemap-feed' ); ?>">
					<?php echo esc_html( $data['path'] ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</th>
			<td><?php if ( $is_pending ) : ?>
				<span class="dashicons dashicons-clock" style="color:#dba617" title="<?php esc_html_e( 'Pending', 'xml-sitemap-feed' ); ?>"></span>
			<?php else : ?>
				<span class="dashicons dashicons-yes-alt" style="color:#00a32a" title="<?php echo esc_html_e( 'Processed', 'xml-sitemap-feed' ); ?>"></span>
			<?php endif; ?>
			</td>
			<td><?php echo esc_html( $last_submitted ); ?></td>
			<td><?php echo esc_html( $last_downloaded ); ?></td>
			<td><?php echo esc_html__( 'Found:', 'xml-sitemap-feed' ) . ' ' . esc_html( $links_submitted ) . '<br>' . esc_html__( 'Indexed:', 'xml-sitemap-feed' ) . ' ' . esc_html( $links_indexed ); ?></td>
			<td style="color:<?php echo $_errors ? '#d63638' : ( $_warnings ? '#dba617' : 'inherit' ); ?>"><?php echo esc_html__( 'Warnings:', 'xml-sitemap-feed' ) . ' ' . esc_html( $_warnings ) . '<br>' . esc_html__( 'Errors:', 'xml-sitemap-feed' ) . ' ' . esc_html( $_errors ); ?></td>
		</tr>
	</tbody>
</table>
