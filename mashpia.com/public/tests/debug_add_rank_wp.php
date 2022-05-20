<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT']."/blog/wp-load.php";

function updateWP( $rank, $user ) {
    $info = getInfo( $rank, $user );
    return import_promotion($info);
}

function getInfo( $rank, $user ) {
    @mysql_select_db("mashpiadb");
    $sql = "select first, last, gender, school_name from users 
				join schools using (school_id) 
				where user_id = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $name = $row['first'] . ' ' . $row['last'];
    $school = $row['school_name'];
    $gender = $row['gender'];
    if (strtolower($gender) == 'm') $gender = "boy";
    else if (strtolower($gender) == 'f') $gender = "girl";

    $sql = "select rank_name from ranks where rank_ord = " . $rank;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $rankName = $row['rank_name'];

    return array(
        'user'		=>	$user,
        'name'		=>	$name,
        'gender'	=>	$gender,
        'school'	=>	$school,
        'rankName'	=>	$rankName
    );
}

function import_promotion($info) {
    $post = array(
        'post_name'		=>	$info['name'],
        'post_title'	=>	$info['name'],
        'post_content'	=>	'',
        'post_status'	=>	'publish',
        'post_type'		=>	'promotion',
        'post_author'	=>	1
    );

    @mysql_select_db("wp");
    $id = wp_insert_post( $post );
    if ($id) {
        add_post_meta( $id, 'user_id', $info['user'] );
        add_post_meta( $id, 'school', $info['school'] );
        add_post_meta( $id, 'gender', $info['gender'] );
        add_post_meta( $id, 'rank', $info['rankName'] );
    }
    @mysql_select_db("mashpiadb");
    return $id;
}

$rank = 7;
$user = 8273;
$post_id = updateWP($rank, $user);
echo "Post ID: " . $post_id;