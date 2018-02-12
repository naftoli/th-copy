<?
session_start();

$code = strtolower($_POST['code']);
if ($code == 'msth5775') {
	$_SESSION['coupon'] = 'msth5775';
	echo 1;
	//echo "Coupon Expired";
} else {
	echo "Invalid Coupon";
}
?>