<?php
ini_set('display_errors',1);

$admin_auth = array('school'); 
require('../../header.php');
if (!$admin_user || $admin_user['auth'] != 'super') {
	echo "Permission Denied.";
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Chidon Reports</title>
	</head>
	
	<body>
			<?
			$files = array();
			if ($handle = opendir(getcwd())) {		
			    while (false !== ($entry = readdir($handle))) {
			        if ($entry != '.' && $entry != '..' && $entry != 'index.php'
                        && strpos($entry, '.') !== false && $entry != 'class.reports.php'
                        && $entry != 'vars.php' && $entry != 'reports.php') {
                            $files[] = $entry;
			        }
			    }
			    closedir($handle);
			}
			
			foreach ($files as $file) {
				$report = ucwords(str_replace('_', ' ', substr($file, 0, strpos($file, '.'))));
				echo "<li><a href='" . $file . "'>" . $report . "</a></li>";
			}
			?>
	</body>
</html>