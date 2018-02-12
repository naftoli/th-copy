<?
include 'db.php';
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
	$blue = 0;
	$purple = 0; 
	if ( isset( $qty['blue'] ) ) 
		$blue += $qty['blue'];
	if ( isset( $qty['purple'] ) ) 
		$purple += $qty['purple'];
	$total = $blue + $purple;
    //charge cc for added amount of haggadas
    $sql = "select * from schools where school_id = " . $id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $card_num = $row['cc_number'];
    $exp_date = $row['cc_exp'];
    $amount = $total * $price;
	$description = "";
	if ( $blue ) 
	    $description .= "Purchased an extra " . $blue . " blue siddurim at " . $price . "/ea. ";
	if ( $blue && $purple ) 
		$description .= ":";
	if ( $purple ) 
	    $description .= "Purchased an extra " . $purple . " purple siddurim at " . $price . "/ea. ";
    $first_name = $row['cc_first'];
    $last_name = $row['cc_last'];
    $address = $row['cc_address'];
    $state = $row['cc_state'];
    $zip = $row['cc_zip'];
    
	$charged = false;
	if ( $id != 82 ) {
    	require "authorize.php";
    
	    $response = "";
	    $charged = false;
	    if ( $response_array[0] == 1 ) {  
	        $response .= $response_array[0] . "\n";
	        $response .= $response_array[3] . "\n";
	        $response .= $response_array[4] . "\n";
	        $response .= $response_array[6] . "\n";
	        $response .= $response_array[9] . "\n";
	        $charged = true;
	    }
	    else {
	        $response .= $response_array[3] . "\n";          
	    }
	} else {
		$charged = true;
	}
    
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