<?php
$link = mysql_connect('67.225.188.200', 'mashpia_user', 'ShJ1uWcT89Ek6E') or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);
