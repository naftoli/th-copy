<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No permission');
}


$stmt = $MASHPIA_DB->prepare("DELETE FROM registration_charges WHERE type = :type AND amount = :amount AND year = :year AND user_id in (
    SELECT user_id FROM users WHERE user_serial = :serial)");

// check if file was uploaded
if (isset($_POST['submit'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $success = true;
        $MASHPIA_DB->beginTransaction();
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $user_serial = $row[0];
            $type = $row[1];
            $amount = floatval($row[2]);
            $res = $stmt->execute(['type' => $type, 'amount' => $amount, 'year' => 5785, 'serial' => $user_serial]);
            if (!$res) {
                $success = false;
                break;
            }
        }
        fclose($handle);
        if ($success) {
            $MASHPIA_DB->commit();
            echo 'Charges removed successfully';
        } else {
            $MASHPIA_DB->rollBack();
            echo 'Error removing charges';
        }
    } else {
        echo 'Error uploading file';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remove Personalized Charges</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<!-- create page to upload a csv file -->
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Remove Personalized Charges</h2>
            <form action="remove_personalized_charges.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="file">Select CSV file to upload:</label>
                    <input type="file" name="file" id="file" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary" name="submit">Upload</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>