<?php
require '../../../db.php';
$user = mysql_real_escape_string($_POST['user']);

//get name and rank
$info = array();
$sql = "select first, last, rank_name from users 
		join rank_marks using (user_id) 
		join ranks using (rank_ord) 
		where rank_ord = (
			select max(rank_ord) from rank_marks where user_id = $user 
		)
		and user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$info['name'] = $row['first'] . ' ' . $row['last'];
$info['rank'] = $row['rank_name'];

echo json_encode($info);
?>