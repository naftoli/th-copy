<?php
ini_set('display_errors', 1);

require 'db.php';
$user_id = 18653;
$sql = "select rank_ord from rank_marks
        where user_id = " . $user_id . "
        order by rank_ord desc
        limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$rank_ord = $row['rank_ord'];

updateWP($rank_ord, $user_id);

$info = array();
function updateWP( $rank, $user ) {
    global $info;
    $info = getInfo( $rank, $user );
    
    require_once "blog/wp-blog-header.php";
    import_promotion();
    echo "Done.";
}

function getInfo( $rank, $user ) {
    mysql_select_db("mashpiadb");
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
    mysql_select_db("wp");
    
    return array(
        'user'		=>	$user, 
        'name'		=>	$name, 
        'gender'	=>	$gender, 
        'school'	=>	$school, 
        'rankName'	=>	$rankName
    );
}

function import_promotion() {
    global $info;
            
    $post = array(
        'post_name'		=>	$info['name'], 
        'post_title'	=>	$info['name'], 
        'post_content'	=>	'', 
        'post_status'	=>	'publish', 
        'post_type'		=>	'promotion', 
        'post_author'	=>	1 
    );
    
    $id = wp_insert_post( $post );
    echo $id;
    add_post_meta( $id, 'user_id', $info['user'] );
    add_post_meta( $id, 'school', $info['school'] );
    add_post_meta( $id, 'gender', $info['gender'] );
    add_post_meta( $id, 'rank', $info['rankName'] );
    
    $term = get_term_by( 'name', $info['school'], 'school' );
    if ( $term ) wp_set_object_terms( $id, (int)$term->term_id, 'school' );
    $term2 = get_term_by( 'name', $info['gender'], 'gender' );
    if ( $term2 ) wp_set_object_terms( $id, (int)$term2->term_id, 'gender' );
    $term3 = get_term_by( 'name', $info['rankName'], 'rank' );
    if ( $term3 ) wp_set_object_terms( $id, (int)$term3->term_id, 'rank' );
    
}