<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if ( http_response_code()!==FALSE ) {
    echo "CLI ONLY"; die();
}

require_once( __DIR__ .'/../../../../db.php');

$date = unixtojd() - 1;
echo "Updating medals earned on $date: \n";

$medals_earned = mysql_query(
    " SELECT subject_id, medal_ord, COUNT(*) AS total, subject_name, medal_name, medal_inventory_id"
    ." FROM medal_marks JOIN subjects USING ( subject_id ) JOIN medals USING ( medal_ord ) "
    ." JOIN medals_inventory USING ( subject_id, medal_ord ) "
    ." WHERE date_awarded = '$date' "
    ." AND medals_inventory.medal_type = 'number_on_back' "
    ." GROUP BY subject_id, medal_ord "
);

while( $medal_earned = mysql_fetch_assoc( $medals_earned ) ){
    echo implode( "\t", [ $medal_earned['total'], $medal_earned['subject_name'], $medal_earned['medal_name'], ] )."\t";
    
    $amount = -$medal_earned['total'];
    $medal_inventory_id = $medal_earned['medal_inventory_id'];
    
    $insert_query = mysql_query(
         " INSERT INTO medals_inventory_details (medal_inventory_id, type, amount) "
        ." VALUES ($medal_inventory_id, 'earned', '$amount');"
    );

    if ( $insert_query ) echo "Updated\n";
    else echo "Database Error\n";
}

echo "\n\nSync Cache Table: ";
// sync with the inventory table
$update_query = mysql_query(
    "UPDATE medals_inventory INNER JOIN ( "
        ." SELECT medal_inventory_id, SUM(amount) AS total FROM medals_inventory_details "
        ." GROUP BY medal_inventory_id "
    .") details USING ( medal_inventory_id ) "
    ."SET in_stock = details.total;"
);
// show the final results
echo !$update_query ? "Success" : "Failed";
