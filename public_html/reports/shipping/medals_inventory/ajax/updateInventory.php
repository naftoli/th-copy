<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

header('Content-Type: application/json; charset=utf-8');

$amount = intval( $_POST['number'] );
$action = $_POST['action'];
$medal_inventory_id = mysql_real_escape_string( $_POST['id'] );

if ( !$medal_inventory_id ) {
    echo json_encode( ['success' => false, 'error' => 'Invalid ID' ] ); die();
}

if ( $amount <= 0 ){
    echo json_encode( ['success' => false, 'error' => 'Invalid Amount'] ); die();
}

$current_total = mysql_query(
    "SELECT in_stock FROM medals_inventory WHERE medal_inventory_id = '$medal_inventory_id';"
);

if ( mysql_num_rows( $current_total ) == 0 ){
    echo json_encode( ['success' => false, 'error' => 'Invalid ID'] ); die();
}

$current_total = mysql_fetch_assoc( $current_total )['in_stock'];

if ( $action == 'add' ) {
    $new_total = $current_total + $amount;
    $entry_type = 'add_to_stock';
} else if ( $action == 'subtract' ) {
    $new_total = $current_total - $amount;
    $entry_type = 'remove_from_stock';
} else {
    echo json_encode( ['success' => false, 'error' => 'Invalid Action'] ); die();
}

if ( $new_total < 0 ) {
    echo json_encode( ['success' => false, 'error' => 'Validation Error: You cannot subtract more then you have.'] ); die();
}

mysql_query('START TRANSACTION;');

$insert_query = mysql_query(
    "INSERT INTO medals_inventory_details ( medal_inventory_id, type, amount ) "
    ."VALUES ('$medal_inventory_id', '$entry_type', '$amount'); "
);

$update_query = mysql_query(
    "UPDATE medals_inventory SET in_stock = '$new_total' WHERE medal_inventory_id = '$medal_inventory_id'; "
);

if ( !$insert_query || !$update_query ) {
    mysql_query('ROLLBACK;');
    echo json_encode( ['success' => false, 'error' => 'Database Error' ] ); die();
} else {
    mysql_query('COMMIT;');
    echo json_encode( ['success' => true ] );
}
