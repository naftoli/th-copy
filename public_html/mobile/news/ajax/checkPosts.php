<?
require "../../../wp/wp-blog-header.php";

$posts = get_posts();
foreach ($posts as $post) {
	setup_postdata( $post );
	the_content();
	echo "<br /><br />";
	wp_reset_postdata();
}
