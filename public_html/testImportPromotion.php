<?
require "wp/wp-blog-header.php";

register_my_post_type();
define_rank_taxonomy();
import_promotion();

function register_my_post_type() {
	$args = array( 
		'publicly_queryable' => true,
		'query_var'          => true 
	);
	register_post_type( 'promotion', $args );
} 

function define_rank_taxonomy() {
	register_taxonomy(
		'rank', 
		'promotion'
	);
	register_taxonomy_for_object_type( 'rank', 'promotion' );
	
	$ranks = getRanks();
	foreach ($ranks as $rank) {
		wp_insert_term( $rank, 'rank' );
	}
}

function getRanks() {
	$ranks = array();
	mysql_select_db("mashpiadb");
	$sql = "select rank_name from ranks order by rank_ord";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$ranks[] = $row['rank_name'];
	}
	mysql_select_db("wp");
	return $ranks;
}

function import_promotion() {
	$info = updateWP(5, 8273);
	
	$post = array(
		'post_name'		=>	$info['name'], 
		'post_title'	=>	$info['name'], 
		'post_content'	=>	'', 
		'post_status'	=>	'publish', 
		'post_type'		=>	'promotion', 
		'post_author'	=>	1 
	);
	$id = wp_insert_post( $post );
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
	echo "Done.";
}

function updateWP( $rank, $user ) {
	mysql_select_db("mashpiadb");
	$sql = "select first, last, gender, school_name from users 
			join schools using (school_id) 
			where user_id = " . $user;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$name = $row['first'] . ' ' . $row['last'];
	$gender = $row['gender'];
	$school = $row['school_name'];
	
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