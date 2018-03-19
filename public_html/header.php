<?
//ini_set('zlib.output_compression', '1');
ini_set('default_charset', 'UTF-8');
mb_internal_encoding("UTF-8");
error_reporting(E_ALL);
setlocale(LC_MONETARY, 'en_US');

// this feature seems to have been removed in php 5.4, what are we running? -hornbacher (http://php.net/manual/en/info.configuration.php#ini.magic-quotes-gpc)
if (get_magic_quotes_gpc()) {
	function stripslashes2(&$val, $key) {
		$val = stripslashes($val);
	}
	
	array_walk_recursive($_GET, 'stripslashes2');
	array_walk_recursive($_POST, 'stripslashes2');
	array_walk_recursive($_COOKIE, 'stripslashes2');
	array_walk_recursive($_REQUEST, 'stripslashes2');
}

$_GETPOST = $_POST + $_GET;
// connect to the database
require_once( $_SERVER['DOCUMENT_ROOT'].'/db.php' );
require_once( $_SERVER['DOCUMENT_ROOT'].'/functions/header.php' );

// authentication imports
if (!isset($_GETPOST['bypass'])) {
	if (!isset($dual_auth)) // $dual_auth is set where before? not in db.php. - hornbacher
		$dual_auth = false;
	
	if ($dual_auth) {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/admin_auth.php' );
		require_once( $_SERVER['DOCUMENT_ROOT'].'/auth.php' );
		
		if (empty($admin_user) && empty($user)) {
			include('login.php');
			exit;
		}
	} 
	elseif (isset($admin_auth)) {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/admin_auth.php' );
	} else {
		require_once( $_SERVER['DOCUMENT_ROOT'].'/auth.php' );
	}
} else {
	$admin_user['admin_id'] = $_GETPOST['admin'];
}

?>