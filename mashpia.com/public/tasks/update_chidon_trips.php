<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get uploaded csv file, parse file with following columns: id, type, amount
if (isset($_FILES['file'])) {
    // parse cvs
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    $data = fgetcsv($handle, 1000, ",");
    $updated = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $id = $data[0];
        $trip = $data[1];
        // update family balance
        $stmt = $MASHPIA_DB->prepare("
            UPDATE th_chidon   
            SET trip = :trip 
            WHERE year = :year 
            AND user_id = (
                SELECT user_id 
                FROM users 
                WHERE user_serial = :id
            )
        ");
        if ($stmt->execute([
            ':id'       => $id,
            ':year'     => $year
        ])) {
            $updated++;
        } else {
            $stmt->errorInfo();
            $stmt->debugDumpParams();
            break;
        }
    }
    echo "Updated $updated trips.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Chidon Trips</title>
</head>
<!-- add ability to upload file -->
<body>
<form action="update_chidon_trips.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload">
</form>
</body>