<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Daily Chitas</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
    </HEAD>

    <BODY onload="iframe_resize();">
        <? include('admin_header.php'); ?>
        <h1>Daily Chitas</h1>
        
        <div id="frame" align="center">
        	<iframe src="http://Kidschitas.org/today" width="600" scrolling="no" id="chitas"></iframe>
        </div>
        
	</body>
</html>