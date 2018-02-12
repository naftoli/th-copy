<?
$admin_auth = array('school'); 
require('header.php'); 
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<title></title>
		<style>
			#dialog {
				display: none;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Try JQuery UI Dialog</h1>

		<div id="dialog"></div>
		
		<input type="button" id="show" value="show modal" />
		
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		<script>
			$(function() {
				$("#show").click(function() {
					$("#dialog").load('admin_profile_modal.php');
					$("#dialog").dialog({
						modal: true, 
					});
				});
			});
		</script>
	</body>
</html>