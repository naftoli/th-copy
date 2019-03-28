<?php
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
    echo "Your Account does not have access to this page."; die();
}

$medal_inventory_id = mysql_real_escape_string( $_POST['id'] );

if ( !$medal_inventory_id ) {
    echo "Server Error: Could not determine Inventory ID"; die();
}

$rows = [];
$query = mysql_query(
    "SELECT * from medals_inventory_details where medal_inventory_id = '$medal_inventory_id'"
);
while ( $row = mysql_fetch_assoc( $query ) ) {
    $rows[] = $row;
}
?>
<table>
    <thead>
        <tr>
            <th>Entry Type</th><th>Amount</th><th>Date</th><th>Running Total</th>
        </tr>
    </thead>
    <tbody>
    <?php 
    if ( count( $rows ) == 0 ) { ?>
        <tr><td colspan='4' style='text-align: center'>No Records Available</td></tr>
    <?php
    } else {
        $running_total = 0;
        foreach( $rows as $row ) { 
            $running_total += $row['amount']?>
        <tr>
            <td><?= str_replace( '_', ' ', $row['type'] ) ?></td>
            <td><?= $row['amount'] ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= $running_total ?></td>
        </tr>
    <?php 
        }
    } ?>
    </tbody>
</table>

