<?php
/**
 * Authors fields
 *
 * @package XML Sitemap & Google News
 */

?>
<fieldset id="xmlsf_authors">
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Authors', 'xml-sitemap-feed' ); ?>
	</legend>
	<p>
		<?php esc_html_e( 'Limit to these authors:', 'xml-sitemap-feed' ); ?>
	</p>
	<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>
	<ul class="cat-checklist">
			<?php
			foreach ( $users as $user ) {
				?>
		<li>
			<label>
				<input type="checkbox" name="xmlsf_authors[]" id="xmlsf_authors_<?php echo esc_attr( $user->ID ); ?>" value="<?php echo esc_attr( $user->ID ); ?>" <?php checked( in_array( (string) $user->ID, (array) $authors, true ) ); ?>/>
				<?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( count_user_posts( $user->ID, $post_types, true ) ); ?>)
			</label>
		</li>
		<?php } ?>
	</ul>
</fieldset>
