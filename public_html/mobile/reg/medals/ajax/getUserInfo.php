<?
chdir('../../../../');
require 'db.php';
$user_id = mysql_real_escape_string( $_POST['user_id'] );

$sql = "select u.first, u.last, u.user_serial, u.mobile_pic, u.user_photo_id, t.thumb, c.class_id, c.class_grade, c.class_sub, c.class_teacher, r.rank_ord, r.rank_name, s.logo  
		from users u 
		join schools s using (school_id) 
		join classes c on c.class_id = u.class_id  
		join rank_marks using (user_id) 
		join ranks r using (rank_ord) 
		left join thumbs t on t.file_id = u.user_photo_id 
		where u.user_id = " . $user_id . "
		order by rank_ord desc limit 1";
        
//echo $sql;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
echo json_encode( $row );
?>