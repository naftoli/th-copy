<?
/*
require "../../../wp/wp-blog-header.php";

$today = date('Y-m-d');
$arrToday = explode('-', $today);

$info = array();
$vars = array(
	'numberposts' 	=> 	-1, 
	'post_type'		=>	'promotion', 
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
		)
	)
);
$posts = new WP_Query( $vars );
$boys = $posts->posts;
foreach ($boys as $index => $post) {
	$post->post_title = ucwords( $post->post_title );
	$meta = get_metadata( 'post', $post->ID );
	$info['boys'][$index]['post'] = $post;
	$info['boys'][$index]['meta'] = $meta;
}

$vars = array(
	'numberposts' 	=> 	-1, 
	'post_type'		=>	'promotion', 
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
		)
	)
);
$posts = new WP_Query( $vars );
$girls = $posts->posts;
foreach ($girls as $index => $post) {
	$post->post_title = ucwords( $post->post_title );
	$meta = get_metadata( 'post', $post->ID );
	$info['girls'][$index]['post'] = $post;
	$info['girls'][$index]['meta'] = $meta;
}
*/

require '../../../db.php';

$info = array();
$sql = "select u.first, u.last, u.gender, s.school_name, max(rm.rank_ord), r.rank_name 
		from users u 
		join schools s using (school_id) 
		join rank_marks rm using (user_id) 
		join ranks r using (rank_ord) 
		where rm.date_promoted = " . unixtojd() . " 
		group by rm.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	if (strtolower($row['gender']) == 'm') {
		$info['boys'][] = array(
			'name'		=>	$row['first'] . ' ' . $row['last'], 
			'school'	=>	$row['school_name'], 
			'rank'		=>	$row['rank_name']
		);
	} else if (strtolower($row['gender']) == 'f') {
		$info['girls'][] = array(
			'name'		=>	$row['first'] . ' ' . $row['last'], 
			'school'	=>	$row['school_name'], 
			'rank'		=>	$row['rank_name']
		);
	}
}

$today = date('Y-m-d');
$arrToday = explode('-', $today);
$str = jdtojewish(gregoriantojd($arrToday[1], $arrToday[2], $arrToday[0]), true,
       CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH); 
$info['date'] = iconv ('WINDOWS-1255', 'UTF-8', $str); 

//echo "<pre>"; print_r( $info ); echo "</pre>";
echo json_encode( $info );
?>