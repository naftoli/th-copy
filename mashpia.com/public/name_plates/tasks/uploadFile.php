<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

// make sure we are super user
if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to access this page.";
    exit;
}

// upload file
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $file['name'];
    $type = $file['type'];
    $size = $file['size'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];

    if ($type != 'text/csv') {
        echo "File is not a csv file.";
        exit;
    }
    
    if ($error == 0) {
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO name_plates 
            SET name_plate_id = '',
            user_id = (
                SELECT user_id FROM users WHERE user_serial = :serial
            ), 
            school_id = :school_id, 
            qty = :qty, 
            missing_he_name = :missing_he_name, 
            reason = :reason
        ");

        $MASHPIA_DB->beginTransaction();
        $success = true;

        $qrys = [];
        // open up csv file
        $handle = fopen($tmp_name, 'r');
        $first = true;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            if ($first) {
                $first = false;
                continue;
            }
            // do something with the data
            $school_id = intval($data[0]);
            $serial = intval($data[1]);
            $qty = intval($data[2]);
            $missing_he_name = intval($data[3]);
            $reason = $data[4];
            
            $res = $stmt->execute([
                ':school_id' => $school_id,
                ':serial' => $serial,
                ':qty' => $qty,
                ':missing_he_name' => $missing_he_name,
                ':reason' => $reason
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
        fclose($handle);

        if ($success) {
            $MASHPIA_DB->commit();
            echo "File uploaded successfully.";
        } else {
            $MASHPIA_DB->rollBack();
            echo "File upload failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
</head>
<body>
    <form action="uploadFile.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>
