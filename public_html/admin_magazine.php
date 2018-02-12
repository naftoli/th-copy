<? 
$admin_auth = array('school','user'); 
require('header.php');

if (isset($_POST['submit'])) {
	//put together date
	$mm = $_POST['month'];
	$dd = (string)$_POST['day'];
	$yy = $_POST['year'];
	
	if (strlen($dd) < 2) {
		$dd = '0' . $dd;
	}
	
	$date = $yy . '-' . $mm . '-' . $dd;
	
	if (isset($_FILES) && $_FILES['file']['size'] > 0) {
		//print_r($_FILES['file']);	
		//move file to permanent location and save then save location into database
		if ($_FILES['file']['error'] == 0) {
			//make sure type is pdf
			if ($_FILES['file']['type'] == 'application/pdf') {
				$name = $_FILES['file']['name']; 
				$destination = "/home/mashpia/public_html/magazine/" . $name;
				if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
					//insert into db
					$id = $admin_user['admin_id'];
					require_once 'db.php';
					$sql = "insert into magazines values( '', '$name', '$date', $id)";
					$result = mysql_query($sql);	
					$msg = "Thank you. Your file has been successfully uploaded.";
				}
			} else {
				$error = "Sorry you have uploaded an invalid file. We can only accept pdf files. Please try again.";
			}
		} else {
			echo $_FILES['file']['error'];
		}
	}
} 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Hachayol Magazine Upload</title>
<style type='text/css'>

</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Hachayol Magazine Upload</h1>

<form action='admin_magazine.php' method='post' enctype="multipart/form-data">
	<div style="color: red">
		<?= isset($error) ? $error : ''; ?>
		<?= isset($msg) ? $msg : '' ; ?>
		<? if (isset($error) || isset($msg)) { ?>
		<br />
		<br />
		<? } ?>
	</div>
	<p>
		Please use the following form to upload the Hachayol Magazine.<br />
		Please make sure to only upload pdf files.
	</p>
	
	<?
		$date = date('Ymd');
		$arr = str_split($date, 2);
		$y = $arr[0] . $arr[1];
		$m = $arr[2];
		$d = $arr[3];
		//echo $y . $m . $d;
	?>
	
	For the week of: Sunday 
	<select name="month">
		<?
			$months = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
			for ($j = 0; $j < count($months); $j++) {
				if ($m == $j) 
					echo "<option value=" . ($j+1) . " selected='selected'>" . $months[$j] . "</option>";
				else 
					echo "<option value=" . ($j+1) . ">" . $months[$j] . "</option>";
			}
		?>
	</select>
	<select name="day">
		<? for ($i = 1; $i < 32; $i++) {
			//find what day was sunday
			$sun = null;
			$day = date('N');
			switch ($day) {
				case '1':
				case '2':
				case '3':
				case '4':
				case '5':
					$sun = $i - $day;
					break;
				case '6':
					$sun = $i + $day;
					break;
				case '7':
					$sun = $i;
					break;
			}
			if ($d == $i)
				echo "<option value=" . $sun . " selected='selected'>" . $sun . "</option>";
			else	
				echo "<option value=" . $sun . ">" . $sun . "</option>";
		} ?>
	</select>
	<select name="year">
		<? for ($i = 2012; $i < 2016; $i++) {
			if ($y == $i)
				echo "<option value=" . $i . " selected='selected'>" . $i . "</option>";
			else 
				echo "<option value=" . $i . ">" . $i . "</option>";
		} ?>
	</select>
	<br />
	<input type="file" name='file'><br />
	<input type="submit" value="upload" name="submit">
</form>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
