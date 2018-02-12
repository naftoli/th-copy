<?
session_start();

$id = $_GET['id'];

//if cart has the promo remove all other cds
if ($id == 100) {
	unset($_SESSION['cart']);
	$_SESSION['cart'][] = 100;
	header("Location: cart.php");
	exit;
}

if (isset($_SESSION['cart'])) {
	//if cart has promo do not add more cds
	if ($_SESSION['cart'][0] != 100) {
		if (is_numeric($id)) {
			$id = intval($id);
			$key = array_search($id, $_SESSION['cart']);
			if ($key === false) {
				$_SESSION['cart'][] = $id;
			}
		}
	}
} else {
	$_SESSION['cart'][] = $id;
}

header("Location: index.php");
exit;
?>