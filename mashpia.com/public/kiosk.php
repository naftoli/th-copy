<?
if (isset($_COOKIE['kiosk_machine'])) 
	setcookie('kiosk_machine', '1', time() + 365*24*60*60, '/'); //refresh cookie
	
header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/statement.php');
//header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/kiosk/store.php');
?>
