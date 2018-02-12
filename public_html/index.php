<? 
//header("Location: under_construction.php");
header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . (isset($_COOKIE['kiosk_machine']) ? '/kiosk.php' : '/admin.php')); ?>
