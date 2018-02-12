<?
include 'db.php';
$price = 14.99;

//parse post and find which school ordered how much
$orders = array();
foreach ( $_POST as $key => $value ) {
    if ( !empty( $value ) && $key != 'submit' ) { 
        $school_id = substr( $key, 3 );
        $orders[$school_id] = $value;
    }
}

foreach ( $orders as $id => $qty ) {
    //charge cc for added amount of haggadas
    $sql = "select * from schools where school_id = " . $id;
    $result = mysql_query( $sql );
    $row = mysql_fetch_assoc( $result );
    $card_num = $row['cc_number'];
    $exp_date = $row['cc_exp'];
    $amount = $qty * $price;
    $description = "Purchase of an extra " . $qty . " haggadas at " . $price . "/ea.";
    $first_name = $row['cc_first'];
    $last_name = $row['cc_last'];
    $address = $row['cc_address'];
    $state = $row['cc_state'];
    $zip = $row['cc_zip'];
    
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
    
    if ( $charged ) {
        $sql = "insert into haggada_purchases set 
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
    header( "Location: haggadas.php?msg=$msg" );  
}
?>