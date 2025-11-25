<?php
/**
 * GSC data section
 *
 * @package XML Sitemap & Google News - Google News Advanced
 */

// Hide the submit button on this page.
?>
<style>
	#submit {
		display: none;
	}
</style>
<?php
// Get connect data.
$options = (array) get_option( 'xmlsf_gsc_connect', array() );
if ( empty( $options['google_refresh_token'] ) ) {
	// Initiate button.
	?>
	<p>
		<?php esc_html_e( 'Connect to Google Search Console to allow sitemap data retrieval.', 'xml-sitemap-feed' ); ?>
	</p>
	<a href="<?php echo esc_url( XMLSF\GSC_Connect::get_settings_url() ); ?>" class="button button-primary">
		<?php esc_html_e( 'Connect', 'xml-sitemap-feed' ); ?>
	</a>
	<?php
	return;
}

$sitemap      = xmlsf()->sitemap_news->get_sitemap_url();
$access_token = XMLSF\GSC_Connect::get_access_token();
$api_endpoint = XMLSF\GSC_Connect::get_api_endpoint( $sitemap );
$result       = XMLSF\GSC_API_Handler::get( $api_endpoint, $access_token );
$property     = XMLSF\GSC_API_Handler::get_property_url( $access_token );

?>
<p><?php esc_html_e( 'Your sitemap data as reported by Google Search Console.', 'xml-sitemap-google-news' ); ?></p>
<?php
if ( isset( $result['success'] ) && $result['success'] && $result['data'] ) {
	$data = $result['data'];
} else {
	$message = ! empty( $result['message'] ) ? $result['message'] : __( 'Empty response.', 'xml-sitemap-feed' );
	// Display error message.
	?>
	<p style="color:#d63638">
		<?php esc_html_e( 'There was an error requesting sitemap data from Google Search Console.', 'xml-sitemap-feed' ); ?>
		<br>
		<?php echo esc_html( $message ); ?>
	</p>
	<p>
		<a href="" class="button button-primary"><?php echo esc_html( translate( 'Retry' ) ); // phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction ?></a>
	</p>
	<form method="post" action="">
		<?php wp_nonce_field( 'sitemap_notifier_manual_submit_action', 'sitemap_notifier_manual_submit_nonce' ); ?>
		<?php submit_button( __( 'Submit Now', 'xml-sitemap-feed' ), 'secondary', 'sitemap_notifier_manual_submit_news', false ); ?>
	</form>
	<?php

	return;
}

$format          = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
$last_submitted  = isset( $data['lastSubmitted'] ) ? wp_date( $format, strtotime( $data['lastSubmitted'] ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$is_pending      = isset( $data['isPending'] ) ? $data['isPending'] : false;
$last_downloaded = isset( $data['lastDownloaded'] ) ? wp_date( $format, strtotime( $data['lastDownloaded'] ) ) : __( 'Unknown', 'xml-sitemap-feed' );
$_warnings       = isset( $data['warnings'] ) ? $data['warnings'] : 0;
$_errors         = isset( $data['errors'] ) ? $data['errors'] : 0;
$gsc_link        = add_query_arg(
	array(
		'resource_id' => $property,
		'sitemap'     => $sitemap,
	),
	'https://search.google.com/search-console/sitemaps/info-drilldown'
);
$links_submitted = 0;
$links_indexed   = 0;
if ( isset( $data['contents'] ) && is_array( $data['contents'] ) ) {
	foreach ( $data['contents'] as $content ) {
		if ( isset( $content['type'] ) && 'news' === $content['type'] ) {
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
			<th><?php esc_html_e( 'XML Sitemap', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Action', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Status', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last submitted', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Last crawled', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'URLs', 'xml-sitemap-feed' ); ?></th>
			<th><?php esc_html_e( 'Issues', 'xml-sitemap-feed' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>
				<?php echo esc_html( $data['path'] ); ?>
			</th>
			<td>
				<form method="post" action="">
					<?php wp_nonce_field( 'sitemap_notifier_manual_submit_action', 'sitemap_notifier_manual_submit_nonce' ); ?>
					<?php submit_button( __( 'Submit Now', 'xml-sitemap-feed' ), 'secondary', 'sitemap_notifier_manual_submit_news', false ); ?>
				</form>
				<a href="<?php echo esc_url( $gsc_link ); ?>" class="button button-secondary" target="_blank">
					<?php esc_html_e( 'View in GSC', 'xml-sitemap-feed' ); ?>
					<span class="dashicons dashicons-external"></span>
				</a>
			</td>
			<td><?php if ( $is_pending ) : ?>
				<span class="dashicons dashicons-clock" style="color:#dba617" title="<?php esc_html_e( 'Pending', 'xml-sitemap-feed' ); ?>"></span>
			<?php else : ?>
				<span class="dashicons dashicons-yes-alt" style="color:#00a32a" title="<?php echo esc_html_e( 'Processed', 'xml-sitemap-feed' ); ?>"></span>
			<?php endif; ?>
			</td>
			<td><?php echo esc_html( $last_submitted ); ?></td>
			<td><?php echo esc_html( $last_downloaded ); ?></td>
			<td><?php echo esc_html__( 'Found:', 'xml-sitemap-feed' ) . ' ' . esc_html( $links_submitted ) . '<br>' . esc_html__( 'Indexed:', 'xml-sitemap-feed' ) . ' ' . esc_html( $links_indexed ); ?></td>
			<td style="color:<?php $_warnings ? '#dba617' : ( $_errors ? '#d63638' : 'inherit' ); ?>"><?php echo esc_html__( 'Warnings:', 'xml-sitemap-feed' ) . ' ' . esc_html( $_warnings ) . '<br>' . esc_html__( 'Errors:', 'xml-sitemap-feed' ) . ' ' . esc_html( $_errors ); ?></td>
		</tr>
	</tbody>
</table>
<p>
	<a href="" class="button button-primary"><?php esc_html_e( 'Refresh data', 'xml-sitemap-feed' ); ?></a>
</p>
