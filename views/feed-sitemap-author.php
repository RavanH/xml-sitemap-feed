<?php
/**
 * XML Sitemap Feed Template for displaying an XML Sitemap feed.
 *
 * @package XML Sitemap Feed plugin for WordPress
 */

defined( 'WPINC' ) || die;

// do xml tag via echo or SVN parser is going to freak out.
echo '<?xml version="1.0" encoding="' . esc_xml( esc_attr( get_bloginfo( 'charset' ) ) ) . '"?>
'; ?>
<?php XMLSF\xml_stylesheet( 'author' ); ?>
<?php do_action( 'xmlsf_generator' ); ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" <?php do_action( 'xmlsf_urlset', 'home' ); ?>>
<?php
$users = get_users(
	apply_filters(
		'xmlsf_get_author_args',
		array(
			'orderby'             => 'post_count',
			'order'               => 'DESC',
			'number'              => '1000',
			'fields'              => array( 'ID', 'user_login', 'spam', 'deleted' ),
			'has_published_posts' => true, // Means all post types by default.
		)
	)
);

foreach ( $users as $user ) {
	$url = apply_filters( 'xmlsf_entry_url', get_author_posts_url( $user->ID ), 'author', $user );

	// Use xmlsf_entry_url filter to return falsy value to exclude a specific URL.
	if ( empty( $url ) ) {
		continue;
	}

	// Allow filtering of users.
	if ( apply_filters( 'xmlsf_skip_user', false, $user ) ) {
		continue;
	}

	do_action( 'xmlsf_url', 'author', $user );

	echo '<url><loc>' . esc_xml( esc_url( $url ) ) . '</loc>';

	$priority = XMLSF\get_user_priority( $user );
	if ( $priority ) {
		echo '<priority>' . esc_xml( $priority ) . '</priority>';
	}

	$lastmod = XMLSF\get_user_modified( $user );
	if ( $lastmod ) {
		echo '<lastmod>' . esc_xml( get_date_from_gmt( $lastmod, DATE_W3C ) ) . '</lastmod>';
	}

	do_action( 'xmlsf_tags_after', 'author', $user );

	echo '</url>';

	$data = array(
		'url'      => $url,
		'priority' => $priority,
		'lastmod'  => $lastmod,
	);
	do_action( 'xmlsf_url_after', 'author', $user, $data );

	echo PHP_EOL;
}
?>
</urlset>
