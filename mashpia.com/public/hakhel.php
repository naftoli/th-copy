<? 
$admin_auth = array('school','user'); 
require('header.php'); 
$end = $_GET['end'];
?>
<!DOCTYPE html>
<html>
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Daily Hakhel Video</title>
		<style>
			p {
				font-size: 14px;
			}
		</style>
	</head>
	
	<body>
	<? include('admin_header.php');?>
	<h1>Daily Hakhel Video</h1>
	
	<? for ($i = 1; $i <= $end; $i++) : ?>
			
		<h4>Day <?=$i?></h4>
		<p>
			Click <a href="hakhel/Part <?=$i?> HD.mp4">here</a> for video.
			<br />Click <a href="hakhel/Phone Translation <?=$i?>.png">here</a> for text/translation of sicha.
		</p>
		
	<? endfor; ?>
	
	</body>
</html>