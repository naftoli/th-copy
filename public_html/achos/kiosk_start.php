<?
/**
*   This is the default page for the kiosk. All preflight stuff can be 
*   boostrapped from here....
*/
setcookie('kiosk_machine', '1', time() + 365*24*60*60, '/');
header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/kiosk.php');
?>
