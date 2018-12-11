<?
include __DIR__ . '/api/header/db.php';
$price = 14.99;

// TODO, update to Authorize.net API.

//parse post and find which school ordered how much
$orders = array();
foreach ( $_POST as $key => $value ) {
    if ( !empty( $value ) && $key != 'submit' ) { 
        $school_id = substr( $key, 3 );
        $orders[$school_id] = $value;
    }
}

foreach ( $orders as $id => $qty ) {
    $school = \School::find([ $id ]);
    //charge cc for added amount of haggadas
    $amount = $qty * $price;
    $description = "Purchase of an extra " . $qty . " haggadas at " . $price . "/ea.";
    $charged = false;
    
    // require "authorize.php";
    
    // $response = "";
    
    // if ( $response_array[0] == 1 ) {  
    //     $response .= $response_array[0] . "\n";
    //     $response .= $response_array[3] . "\n";
    //     $response .= $response_array[4] . "\n";
    //     $response .= $response_array[6] . "\n";
    //     $response .= $response_array[9] . "\n";
    //     $charged = true;
    // }
    // else {
    //     $response .= $response_array[3] . "\n";          
    // }
    
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