<?
session_start();

if (!isset($_SESSION['cart'])) {
	header("Location: cart.php");
	exit;
}

require_once 'db.php';

$expected = array( 'fname', 'lname', 'email', 'zip', 'ccnumber', 'mm', 'yy', 'cvv', 'amount' );
$errors = array();

foreach ( $expected as $key ) {
	if ( !isset( $_POST[$key] ) ) {
		$errors[] = "Error: THKK-KJJL";
		exit;
	} else {
		$$key = mysql_real_escape_string( $_POST[$key] );
	}
}

if ( !ctype_alpha( $fname ) || !ctype_alpha( $lname ) ) {
	$errors[] = "First and Last Name can only contain letters.";
}

if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
	$errors[] = "You have entered an incorrect email.";
}

if ( !is_numeric( $ccnumber ) || !is_numeric( $mm ) || !is_numeric( $yy ) || !is_numeric( $cvv ) ) {
	$errors[] = "Credit Card Number, Expiry Date, and CVC can only contain numbers.";
}

if ( strlen( $mm ) != 2 || strlen( $yy ) != 2 ) {
	$errors[] = "Expiry Month and Year must be two digits.";
}

if ( !empty( $errors ) ) {
	$errors[] = "error";
	echo json_encode( $errors );
	exit;	
}

require_once 'authorize.php';

if ( $response_array[0] == 1 ) {
	//add to purchases db and send email
	$response = "";
	foreach ($response_array as $k => $item) {
		if (!empty($item)) {
			$response .= $item . ':';
		}
	}

	require_once '../classes/purchase.php';
	$name = $fname . ' ' . $lname;
	try {
		$p = new Purchase( $name, $email, $amount, $_SESSION['cart'], $response );
		echo json_encode( array($p->createPurchase()) );
	} catch ( Exception $e ) {
		echo json_encode( array($e->getMessage()) );
	}
} else {
	echo json_encode( $response_array );
}
exit;
?>