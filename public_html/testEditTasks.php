<?
$admin_auth = array('school'); 
require('header.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Edit Tasks</title>
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <script type="text/javascript">
        	$(function() {
        		$(".sSelect").sSelect();
        	});
        </script>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Edit Tasks</h1>
 		<? if ($admin->auth == 'super') { ?>
	        <form action="testEditTasks.php" method="post">
	        	<select name='school' class='sSelect'>
	        		<option value='0'>Select School</option>
	        		<option value='-1'>All Schools</option>
	        		
	        	</select>
	        </form>
	    <? } ?>
    </body>
</html>