<?php
	//db connection 
	$link = mysql_connect('http://50.22.1.85', 'mashpia', 'eZauPhy9CEqEdYDT') or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
	mysql_query('SET NAMES utf8');
	mysql_query('SET CHARACTER_SET utf8');
	mysql_select_db('mashpia') or trigger_error_server('Failed to select db', E_USER_ERROR);
?>
