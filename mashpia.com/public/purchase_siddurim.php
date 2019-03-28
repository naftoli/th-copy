<?
include __DIR__ . '/api/header/db.php';
$price = 25.00;

//parse post and find which school ordered how much
$orders = array();
foreach ( $_POST as $key => $value ) {
    if ( !empty( $value ) && $key != 'submit' ) {
    	$i = strrpos( $key, 'y' );
        $school_id = substr( $key, $i+1 );
		if ( strstr( $key, 'blue') ) {
        	$orders[$school_id]['blue'] = $value;
		} else {
			$orders[$school_id]['purple'] = $value;
		}
    }
}

foreach ( $orders as $id => $qty ) {
	// load model
	$school = \School::find([ $id ]);

	$blue = 0;
	$purple = 0; 
	if ( isset( $qty['blue'] ) ) 
		$blue += $qty['blue'];
	if ( isset( $qty['purple'] ) ) 
		$purple += $qty['purple'];
	$total = $blue + $purple;

	$amount = $total * $price;
	
	$description = "";
	if ( $blue ) 
	    $description .= "Purchased an extra " . $blue . " blue siddurim at " . $price . "/ea. ";
	if ( $blue && $purple ) 
		$description .= ":";
	if ( $purple ) 
	    $description .= "Purchased an extra " . $purple . " purple siddurim at " . $price . "/ea. ";
    
	$charged = false;

	// TODO, charge model's card on file.
	// if ( $id != 82 ) {
    // 	require "authorize.php";
    
	//     $response = "";
	//     $charged = false;
	//     if ( $response_array[0] == 1 ) {  
	//         $response .= $response_array[0] . "\n";
	//         $response .= $response_array[3] . "\n";
	//         $response .= $response_array[4] . "\n";
	//         $response .= $response_array[6] . "\n";
	//         $response .= $response_array[9] . "\n";
	//         $charged = true;
	//     }
	//     else {
	//         $response .= $response_array[3] . "\n";          
	//     }
	// } else {
	// 	$charged = true;
	// }
    
    if ( $charged ) {
        $sql = "insert into siddur_purchases set 
                school_id = " . $id . ", 
                admin_id = 0, 
                description = '" . $description . "', 
                paid = " . $amount . ", 
                cc_auth = '" . $response . "', 
                date = now()";
        //echo $sql;
        @mysql_query( $sql );
        $msg = "Your card was successfully charged $" . $amount . ".<br />Thank You!<br />";
    } else {
        $msg = "We're sorry but your card was declined. Reason: " . $response . "<br />Please try again.<br />";
    }
    $msg = urlencode( $msg );
    header( "Location: siddurim.php?msg=$msg" );  
}
?>