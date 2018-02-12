<?
include("db.php");

$sql = "SELECT class_id, class_grade, class_sub FROM classes WHERE school_id=" . $_GET['school_id'];
$query = mysql_query($sql);

//include("classes/user.php");

//$users = array();
//$sql = "SELECT * FROM users WHERE school_id=" . $_GET['school_id'];
//$query = mysql_query($sql);
//while ($row = mysql_fetch_assoc($query))
//{
//	$user = new user($row);
//	array_push($users, $user);
//}
//$sql = "SELECT user_id, first, last, first_he, last_he, username, user_code, user_serial, school_name, school_city, school_state, school_number, school_logo_id, class_grade, class_sub, user_photo_id, dob, dob_he_offset, user_start_date, rank_ord, rank_name, rank_image_id, rank_color, rank_background_image_id ";
//$sql = $sql . "FROM users AS u ";
//$sql = $sql . "LEFT JOIN schools AS s USING (school_id) ";
//$sql = $sql . "LEFT JOIN classes AS c USING (school_id, class_id) ";
//$sql = $sql . "LEFT JOIN rank_marks AS rm ON (u.user_id=rm.user_id) ";
//$sql = $sql . "WHERE u.user_start_date IS NOT NULL ";
//$sql = $sql . "AND u.school_id=" . $_GET['school_id'] . " ";

//' . ($school_id != -1 ? " AND school_id = $school_id" : '') . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') . ($rank_ord != -1 ? " AND rank_ord = $rank_ord" : '') . ($hide_printed && $rank_type != 'all' ? ' AND date_printed IS NULL' : '') . ' ORDER BY school_name, class_grade, class_sub, last, first, username, rank_ord';
?>

	<li>
		<a href="JavaScript:void(0);" class="hiLite">All Platoons</a>
	</li>
	
<? while ($row = mysql_fetch_assoc($query)) : ?>
	<li>
		<? if ($row['class_sub'] != "") : ?>
		<a id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this);"><?=$row['class_grade'];?> - <?=$row['class_sub'];?></a>
		<? else : ?>
		<a id="select_anchor_tag" href="JavaScript:void(0);" onclick="anchor_tag_click(this);"><?=$row['class_grade'];?></a>
		<? endif; ?>
	</li>
<? endwhile?>
