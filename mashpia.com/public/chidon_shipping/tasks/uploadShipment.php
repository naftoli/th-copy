<?php
ini_set('display_error', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO th_chidon_shipping 
    SET user_id = :user, 
        item_id = :item, 
        year = :year, 
        status = 1, 
        item_num = 0, 
        date_shipped = now(), 
        shipment_number = :number 
");

if (isset($_POST['submit'])) {
    $file_info = $_FILES['file'];
    
    // load file and loop through rows
    $file = fopen($file_info['tmp_name'], 'r');
    while (($row = fgetcsv($file)) !== FALSE) {
        // first element is item ID, second element is user ID
        $itemID = $row[0];
        $userID = $row[1];
        $stmt->execute([
            'user' => $userID,
            'item' => $itemID,
            'year' => $year,
            'number' => $_POST['shipment_number']
        ]);
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Upload Shipment</title>
    </head>
    <body>
        <h1>Upload Shipment</h1>
        <!-- create file upload form -->
        <form action="uploadShipment.php" method="post" enctype="multipart/form-data">
            <input type="file" name="file" id="file"><br /><br />
            <select name='shipment_number' id='shipment_number'>
                <option value='1'>1</option>
                <option value='2'>2</option>
                <option value='3'>3</option>
            </select><br /><br />
            <input type="submit" value="Upload" name="submit">
        </form>
    </body>
</html>
