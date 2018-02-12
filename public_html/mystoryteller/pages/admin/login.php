<?
session_start();
if (isset($_SESSION['login']) && $_SESSION['login'] == 2) {
	header("Location: reports.php");
	exit;
}

$sendBack = false;
if (!isset($_POST['username']) || !isset($_POST['password'])) {
	$sendBack = true;
}

$username = 'perlcds';
$password = 'th5775';
if ($username != trim($_POST['username']) || $password != trim($_POST['password'])) {
	$sendBack = true;
} else {
	$_SESSION['login'] = 2;
}

if ($sendBack) {
	header("Location: index.php");
	exit;
} else {
	header("Location: reports.php");
	exit;
}
?>