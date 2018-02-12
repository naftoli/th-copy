<?
session_start();

$id = $_POST['id'];
if (is_numeric($id)) {
	$k = array_search($id, $_SESSION['cart']);
	if ($k !== false) {
		unset($_SESSION['cart'][$k]);
		echo 1;
	} else {
		echo "Could not find item in cart.";
	}
} else {
	echo "Invalid ID.";
}
?>