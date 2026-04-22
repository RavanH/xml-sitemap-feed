<?php
/**
 * GSC log ection
 *
 * @package XML Sitemap & Google News - XML Sitemap Advanced
 */

if ( empty( $log ) ) {
	$log = array(
		array (
			'time' => time(),
			'message' => esc_html__( 'No log entries found.', 'xml-sitemap-feed' ),
			'status' => 'info'
		),
	);
}

$format = get_option( 'date_format' ) . ' @ ' . get_option( 'time_format' );
?>
<p>
	<?php esc_html_e( 'The 20 most recent sitemap notification request and related messages are logged here.', 'xml-sitemap-feed' ); ?>
	<?php echo $advanced; ?>
</p>
<table class="widefat">
	<thead>
		<tr>
			<th><?php echo esc_html_x( 'Date', 'column name' ); ?></th>
			<th><?php esc_html_e( 'Message', 'xml-sitemap-feed' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( array_reverse( (array) $log ) as $entry ) :
			if ( ! is_array( $entry ) ) { continue; }

			$date = ! empty( $entry['time'] ) ? wp_date( $format, $entry['time'] ) : '';
			$msg  = ! empty( $entry['message'] ) ? $entry['message'] : '';
			?>
			<tr>
				<td><?php echo esc_html( $date ); ?></td>
				<td class="notice-<?php echo isset( $entry['status'] ) ? esc_attr( $entry['status'] ) : 'info'; ?>" style="border-left-width: 4px;border-left-style: solid"><?php echo esc_html( $msg ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
