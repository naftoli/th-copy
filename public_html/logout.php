<?
session_start();
session_unset();
session_destroy();

unset($_SESSION);
unset($user);
unset($admin_user);
unset($admin_auth);
/*
unset($_COOKIE['PHPSESSID']);
unset($_COOKIE['user_id']);
unset($_COOKIE['auth']);
unset($_COOKIE['admin_id']);
unset($_COOKIE['admin_auth']);
unset($_COOKIE['camp_id']);
unset($_COOKIE['admin_username_default']);
unset($_COOKIE['username_default']);
unset($_COOKIE['PHPSESSID']);
unset($_COOKIE['comm100_27681']);
*/
setcookie('user_id', '', time()-86400, '/');
setcookie('auth', '', time()-86400, '/');
setcookie('admin_id', '', time()-86400, '/');
setcookie('admin_auth', '', time()-86400, '/');
setcookie('admin_username_default','',time()-86400,'/');
setcookie('username_default','',time()-86400,'/');
setcookie('PHPSESSID','',time()-86400,'/');
setcookie('camp_id','',time()-86400,'/');

if (isset($_GET['n']) && $_GET['n'] !== '') {
    $page = '/' . $_GET['n'];
}
elseif (isset($_COOKIE['kiosk_machine'])) {
    $page = '/kiosk.php';
} 
else {
    $page = '/';
}

header('Location: http://mashpia.com' . $page);
?>
