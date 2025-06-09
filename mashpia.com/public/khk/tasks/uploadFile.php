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
            INSERT INTO khk_info_5785 
            SET 
                user_serial = :serial,
                amount_passed = :amount_passed, 
                `5782` = :5782,
                `5783` = :5783,
                `5784` = :5784,
                `5785` = :5785
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
            $user_serial = intval($data[0]);
            $amount_passed = intval($data[1]);
            $passed_5782 = $data[2];
            $passed_5783 = $data[3];
            $passed_5784 = $data[4];
            $passed_5785 = $data[5];
            
            $res = $stmt->execute([
                ':serial' => $user_serial,
                ':amount_passed' => $amount_passed,
                ':5782' => $passed_5782,
                ':5783' => $passed_5783,
                ':5784' => $passed_5784,
                ':5785' => $passed_5785
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
            $stmt->debugDumpParams();
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
