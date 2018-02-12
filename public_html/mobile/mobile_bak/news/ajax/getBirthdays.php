<?
//require "../../../blog/wp-blog-header.php";
ini_set('display_errors', 1);
require_once "../../../db.php";

function getRank( $user ) {
	//mysql_select_db('mashpiadb');
	$sql = "select r.rank_name 
			from ranks r 
			join rank_marks rm using (rank_ord) 
			where rm.user_id = " . $user . " 
			order by rank_ord desc 
			limit 1";
	$result = mysql_query( $sql );
	$row = mysql_fetch_assoc($result);
	//mysql_select_db('wp');
	return $row['rank_name'];
}

$today = date('Y-m-d');
$arrToday = explode('-', $today);
/*
$info = array();
$vars = array(
	'numberposts' 	=> 	-1, 
	'post_type'		=>	'birthday', 
	'date_query'	=>	array(
		'year'	=> 	$arrToday[0], 
		'month'	=>	$arrToday[1], 
		'day'	=>	$arrToday[2]
	), 
	'meta_query'	=> 	array(
		array(
			'key'		=>	'gender', 
			'value'		=>	'boy', 
			'compare'	=>	'='
		), 
		array(
			'key'		=>	'registered', 
			'value'		=>	'1', 
			'compare'	=>	'='
		)
	)
);
$posts = new WP_Query( $vars );
$boys = $posts->posts;
if (!empty($boys)) {
	foreach ($boys as $index => $post) {
		$post->post_title = ucwords( $post->post_title );
		$meta = get_metadata( 'post', $post->ID );
		//get current user's rank
		$user_id = $meta['user_id'];
		$user = $user_id[0];
		$rank = getRank( $user );
		$info['boys'][$index]['post'] = $post;
		$info['boys'][$index]['meta'] = $meta;
		$info['boys'][$index]['rank'] = $rank;
	}
} else {
	$info['boys'] = array();
}

$vars = array(
	'numberposts' 	=> 	-1, 
	'post_type'		=>	'birthday', 
	'date_query'	=>	array(
		'year'	=> 	$arrToday[0], 
		'month'	=>	$arrToday[1], 
		'day'	=>	$arrToday[2]
	), 
	'meta_query'	=> 	array(
		array(
			'key'		=>	'gender', 
			'value'		=>	'girl', 
			'compare'	=>	'='
		), 
		array(
			'key'		=>	'registered', 
			'value'		=>	'1', 
			'compare'	=>	'='
		)
	)
);
$posts = new WP_Query( $vars );
$girls = $posts->posts;
if (!empty($girls)) {
	foreach ($girls as $index => $post) {
		$post->post_title = ucwords( $post->post_title );
		$meta = get_metadata( 'post', $post->ID );
		//get current user's rank
		$user_id = $meta['user_id'];
		$user = $user_id[0];
		$rank = getRank( $user );
		$info['girls'][$index]['post'] = $post;
		$info['girls'][$index]['meta'] = $meta;
		$info['girls'][$index]['rank'] = $rank;
	}
} else {
	$info['girls'] = array();
}
*/

$info = array();
$sql = "select * from wp.wp_posts where post_type = 'birthday' and post_date = '" . $today . "'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$meta = array();
	$sql2 = "select * from wp.wp_postmeta where post_id = " . $row['ID'];
	$result2 = mysql_query($sql2);
	while ($row2 = mysql_fetch_assoc($result2)) {
		$meta[$row2['meta_key']] = $row2['meta_value'];
	}
	$info[$meta['gender'].'s'][] = array(
		'post' => array('post_title' => ucwords($row['post_title'])),
		'meta' => array('school' => $meta['school'], 'age' => $meta['age']),
		'rank' => getRank($meta['user_id'])
	);
}

$str = jdtojewish(gregoriantojd($arrToday[1], $arrToday[2], $arrToday[0]), true,
       CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH); 
$info['date'] = iconv ('WINDOWS-1255', 'UTF-8', $str); 

//echo "<pre>"; print_r( $info ); echo "</pre>";
echo json_encode( $info );
?>