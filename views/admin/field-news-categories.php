<?php
/**
 * Categories fields
 *
 * @package XML Sitemap & Google News
 */

$options             = (array) get_option( 'xmlsf_news_tags', array() );
$selected_categories = isset( $options['categories'] ) && is_array( $options['categories'] ) ? $options['categories'] : array();

if ( function_exists( '\pll_languages_list' ) ) {
	add_filter(
		'get_terms_args',
		function ( $args ) {
			$args['lang'] = '';
			return $args;
		}
	);
}

global $sitepress;
if ( $sitepress ) {
	remove_filter( 'get_terms_args', array( $sitepress, 'get_terms_args_filter' ) );
	remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
	remove_filter( 'terms_clauses', array( $sitepress, 'terms_clauses' ) );
}

$cat_list = str_replace(
	'name="post_category[]"',
	'name="xmlsf_news_tags[categories][]"',
	wp_terms_checklist(
		null,
		array(
			'taxonomy'      => 'category',
			'selected_cats' => $selected_categories,
			'echo'          => false,
		)
	)
);
?>
<fieldset>
	<legend class="screen-reader-text">
		<?php esc_html_e( 'Categories' ); ?>
	</legend>
	<p>
		<?php esc_html_e( 'Limit to posts in these post categories:', 'xml-sitemap-feed' ); ?>
	</p>
	<style>ul.cat-checklist{height:auto;max-height:48em}ul.children{padding-left:1em}</style>
	<ul class="cat-checklist">
		<?php echo $cat_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</ul>
</fieldset>
