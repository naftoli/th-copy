<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
		<?php
		require_once '../../db.php'; // ~/public_html/db.php
		echo mysql_real_escape_string('hi there');
		echo "<br>";
		echo var_dump(calculateSM( 5777 ));
		?>
    </body>
</html> 
