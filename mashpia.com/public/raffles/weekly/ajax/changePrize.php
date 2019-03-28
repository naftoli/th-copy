<?php

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

// admins only
if ( $admin_user['auth'] !== "super" ) {
    echo json_encode([
        "success" => false, "error" => "Invalid permissions"
    ]);
    die();
}

// clean post params
$prize_id       = mysql_real_escape_string( $_POST['prize_id']      );
$raffle_id      = mysql_real_escape_string( $_POST['raffle_id']     );
$old_prize_id   = mysql_real_escape_string( $_POST['old_prize_id']  );

// invalid params
if ( !$prize_id || !$raffle_id || !$old_prize_id ) {
    echo json_encode([
        "success" => false, "error" => "Invalid request"
    ]);
    die();
}
// start the transaction
mysql_query("START TRANSACTION;");

// update the prize relationship in the DBS
$update_query_raffles = mysql_query(
    "UPDATE raffle_prizes 
    SET prize_id='$prize_id' 
    WHERE raffle_id='$raffle_id' 
    AND prize_id='$old_prize_id';"
);
// update all winners of this prize in the raffle to the new prize
$update_query_winners = mysql_query(
    "UPDATE raffle_winners 
    SET prize_id='$prize_id' 
    WHERE raffle_id='$raffle_id' 
    AND prize_id='$old_prize_id';"
);

// bad query results
if ( !$update_query_winners || !$update_query_raffles ) {
    mysql_query("ROLLBACK;"); // rollback any changes
    echo json_encode([
        "success" => false, "error" => "Could not update prize, please try again later"
    ]);
    die();
} else {
    mysql_query("COMMIT;"); // save the updates
}
// alls good
echo json_encode( [ "success" => true ] )
?>