<? require('header.php'); 
require('calendar.php');
require('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, IFNULL(class_grade+0, -1) class_grade_ord, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

?>


<html>

	<head>
	
		<script type="text/javascript">	
		</script>

	</head>


	<body>
		<p>
			This site currently only supports recent versions of <a href="http://www.mozilla.org/products/firefox/" rel="no follow">Firefox</a>.
			<br />
			Firefox is a web standards based browser, you can download and easily install it
			<a href="http://www.mozilla.org/products/firefox/" rel="no follow">here</a>. 
		</p>
		<p>We are currently working on support for other browsers.</p>
		<p>Apologies for the inconvenience</p>
	</body>

</html>
