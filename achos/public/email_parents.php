<? 
if (isset($_POST['submit'])) {
	//get schools ids
	$ids = array();
	foreach ($_POST['schools'] as $school) {
		$ids[] = $school;
	}
	
	//get emails of parents
	require_once('db.php');
	if (in_array('0', $ids)) {
		$sql = "select distinct first, last, admin_email from admins 
			join admin_auths on (admins.admin_id = admin_auths.admin_id and admin_auths.auth = 'user')";
	} else {
		$str = "(";
		for ($k = 0; $k < count($ids); $k++) 
			if ($k == (count($ids) - 1)) 
				$str .= $ids[$k] . ")";
			else 
				$str .= $ids[$k] . ", ";
		$sql = "SELECT a.first, a.last, a.admin_email 
			FROM admins AS a, admin_auths AS aa, users AS u
			WHERE a.admin_id = aa.admin_id
			AND aa.auth = 'user'
			AND u.user_id = aa.id
			AND u.school_id in " . $str;
	}
	$result = mysql_query($sql);
	$parents == array();
	$i = 0;
	while ($row = mysql_fetch_assoc($result)) {
		$parents[$i]['first'] = $row['first'];
		$parents[$i]['last'] = $row['last'];
		$parents[$i]['email'] = $row['admin_email'];
		$i++;
	}
	
	//mail to all parents
	$to = '';
	foreach ($parents as $parent) {
		if (filter_var($parent['email'], FILTER_VALIDATE_EMAIL)) 
			$to .= $parent['email'] . ";";
	}
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
	$headers = "From: th@tzivoshashem.org";
	
	echo "To: " . $to . "<br />";
	echo "<br />";
	echo "Subject: " . $subject . "<br />";
	echo "Message: " . $message . "<br />";
	exit;
	
	if (mail($to, $subject, $message, $headers)) {
		echo "Message successfully sent.";
	} else {
		echo "There's been a problem sending your email, please contact your systems administrator";
	}
	exit;
}
$admin_auth = array('school','user'); 
require('header.php'); 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Send email to Parents</title>
	</head>

	<body>
		<? include('admin_header.php');?>
		
		<? if ($admin->auth == 'super') : ?>
		<h1>Email to Parents</h1>
		
		<form action='email_parents.php' method='post' enctype="multipart/form-data">
		
	<?
	//get list of schools to send to
	require_once('db.php');
	$schools = array();
	$i = 0;
	$sql = "select school_id, school_name from schools order by school_name";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_row($result)) {
		$schools[$i]['id'] = $row[0];
		$schools[$i++]['name'] = $row[1];
	}
	?>
	
			Subject: <input type='text' name='subject' size=40></input>
			<br />
			<br />
			Message:
			<br />
			<textarea name='message' rows=20 cols=60></textarea>
			<br />
			<br />
	
			Select the school(s) you would like to email to:<br />
			<select name='schools[]' multiple='multiple' size=<?=count($schools)+1?>>
				<option value='0'>All Schools
				<? for ($j = 0; $j < count($schools); $j++) : ?>
				<option value="<?=$schools[$j]['id'];?>"><?=$schools[$j]['name'];?>
				<? endfor; ?>
			</select>
				
			<br />
	
			Select a file to upload: <input type="file" name="uploaded_file"><br />
			
			<input type='submit' name='submit' value='Email'>
		</form>
		<? else : ?>
		no permission to view this page
		<? endif; ?>
		<!-- if ($admin->auth == 'super') -->
	
	</body>
	
</html>
