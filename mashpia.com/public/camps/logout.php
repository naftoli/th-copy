<?
session_start();
$_SESSION['camp_id'] = "";
if(isset($_GET['n']) && $_GET['n'] !== '') {
  $page = '/' . $_GET['n'];
} elseif(isset($_COOKIE['kiosk_machine'])) {
  $page = '/kiosk.php';
} else {
  $page = '/';
}
$page = '/home.php';
setcookie('user_id', '', time() - 86400, '/');
setcookie('auth', '', time() - 86400, '/');
unset($user);
unset($_COOKIE['user_id']);
unset($_COOKIE['auth']);
setcookie('admin_id', '', time() - 86400, '/');
setcookie('admin_auth', '', time() - 86400, '/');
unset($admin_user);
unset($_COOKIE['admin_id']);
unset($_COOKIE['admin_auth']);
	//header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . $page);
header('Location: http://mashpia.com/admin.php');
	?>
