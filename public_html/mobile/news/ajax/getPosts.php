<?
require "../../../blog/wp-blog-header.php";

$paged = isset( $_POST['page'] ) && $_POST['page'] ? $_POST['page'] : false;
$postsPerPage = 10;

$search = false;
if (isset($_POST['search']) && !empty($_POST['search'])) {
	$search = true;
	$vars = array(
	  'posts_per_page' => $postsPerPage,
	  'paged'          => $paged, 
	  's'			   => trim($_POST['search'])
	);
} else if ( isset( $_POST['postID'] ) && !empty( $_POST['postID'] ) ) {
	$vars = array(
		'p'	=>	(int)$_POST['postID']
	);
} else {
	$vars = array(
	  'posts_per_page' => $postsPerPage,
	  'paged'          => $paged
	);
}

$posts = get_posts( $vars );

$p = get_posts( array(
	'numberposts' => -1
));
$numPosts = $search ? count( $posts ) : count( $p );

$data = array();

foreach ($posts as $post) {
	$args = array(
	   'post_type' => 'attachment',
	   'numberposts' => -1,
	   'post_status' => null,
	   'post_parent' => $post->ID
	);
	
	/*
	$attachments = get_posts( $args );
	$images = array();
    if ( $attachments ) {
        foreach ( $attachments as $attachment ) {
        	$images[] = wp_get_attachment_image_src( $attachment->ID, 'full' );
		}
	} 
  	*/
  	$images = array();
	setup_postdata( $post );
	$content = isset( $_POST['postID'] ) && !empty( $_POST['postID'] ) ? $post->post_content : get_the_content();
	
	$data[] = array(
		'title' 	=> 	$post->post_title, 
		'content'	=>	wpautop( $content ), 
		'posted'	=>	$post->post_date, 
		'images'	=>	$images, 
	);
	wp_reset_postdata();	
}

if ($numPosts > $postsPerPage) {
	$numPages = $numPosts / $postsPerPage;
	if ( $numPosts % $paged ) $numPages++;
} else {
	$numPages = 1;
}

$info['pageInfo'] = array(
	'page'		=>	$paged, 
	'total'		=>	$numPages
);
$info['data'] = $data;

//echo "<pre>"; print_r( $info ); echo "</pre>";
echo json_encode( $info );
?>