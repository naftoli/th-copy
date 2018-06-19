<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}

$medals = [];
$query = mysql_query(
     " SELECT medals_inventory.*, subject_name, medal_name FROM medals_inventory "
    ." JOIN subjects USING (subject_id) JOIN medals USING (medal_ord) "
    ." ORDER BY subject_id, medals_inventory.medal_ord, medal_type DESC"
);
while ( $row = mysql_fetch_assoc( $query ) ) {
    $medals[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Medals Inventory Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/raffles/shared/styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="../css/shipping_form.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <style>
            .inline-input input[type="number"] { width: 50%; margin: 0px; background: none; border: none; border-bottom: 1px solid; }
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Medals Inventory Report</h1>

        <div id='report'>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th><th>Medal</th><th>Type</th><th>Stock</th><th>Details</th><th>Add/Subtract</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach( $medals as $medal ) { ?>
                <tr>
                    <td><?= $medal['subject_name'] ?></td>
                    <td><?= $medal['medal_name'] ?></td>
                    <td><?= str_replace( '_', ' ', $medal['medal_type'] ) ?></td>
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
        </div>
        <div class="modal" id="details-modal">
            <div class="modal-content">
                <h1>
                    <span id="modal-title">Medal Inventory Log</span>
                    <span class="close modal_exit">×</span>
                </h1>
                <div id='inventory-details'></div>
            </div>
        </div>
        <script src="index.js"></script>
    </body>
</html>