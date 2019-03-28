<?php
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}

$type = mysql_real_escape_string( $_POST['type'] );

$medals = [];
$query = mysql_query(
     " SELECT medals_inventory.*, subject_name, medal_name FROM medals_inventory "
    ." JOIN subjects USING (subject_id) JOIN medals USING (medal_ord) "
    ." WHERE medal_type = '$type' AND subjects.subject_id != 106"
    ." ORDER BY subject_id, medals_inventory.medal_ord"
);
while ( $row = mysql_fetch_assoc( $query ) ) {
    $medals[] = $row;
}

?>

<table>
    <thead>
        <tr>
            <th>Subject</th><th>Medal</th><th>Stock</th><th>Details</th><th>Add/Subtract</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach( $medals as $medal ) { ?>
    <tr>
        <td><?= $medal['subject_name'] ?></td>
        <td><?= $medal['medal_name'] ?></td>
        <!-- <td><?= str_replace( '_', ' ', $medal['medal_type'] ) ?></td> -->
        <td id='stock-<?=$medal['medal_inventory_id']?>'><?= $medal['in_stock'] ?></td>
        <td><a class='button show-details' data-id='<?= $medal['medal_inventory_id'] ?>'>View Details</a></td>
        <td>
            <div class='inline-input'>
                <input type='number' value='0'/>
                <a class='button subtract-inventory' data-id='<?= $medal['medal_inventory_id'] ?>'>-</a>    
                <a class='button add-inventory' data-id='<?= $medal['medal_inventory_id'] ?>'>+</a>
            </div>
        </td>
    </tr>    
    <?php } ?>
    </tbody>
</table>