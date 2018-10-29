<?php
chdir('../../../../');
require 'db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

$sql = "SELECT u.first, u.last, u.first_he, u.last_he, u.lang_id, u.user_serial, u.mobile_pic, u.user_photo_id, t.thumb, "
    ." c.class_id, c.class_grade, c.class_sub, c.class_teacher, r.rank_ord, r.rank_name, s.logo, s.logo_girls, u.gender "
	." FROM users u "
	." JOIN schools s USING (school_id) "
	." LEFT JOIN classes c ON c.class_id = u.class_id "
	." LEFT JOIN rank_marks USING (user_id) "
	." LEFT JOIN ranks r USING (rank_ord) "
	." LEFT JOIN thumbs t ON t.file_id = u.user_photo_id "
	." WHERE u.user_id = " . $user_id . " "
	." ORDER BY rank_ord DESC LIMIT 1";
        
//echo $sql;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo json_encode( $row );
?>