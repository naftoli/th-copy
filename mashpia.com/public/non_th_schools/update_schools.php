<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "You do not have permission to access this page.";
    exit;
}

$stmtUpdate = $MASHPIA_DB->prepare(
    "UPDATE non_th_schools 
            SET school_name = :school_name, city = :city, state = :state, zip = :zip, country = :country, phone = :phone 
            WHERE non_th_school_id = :school_id"
);

$stmtInsert = $MASHPIA_DB->prepare(
    "INSERT INTO non_th_schools (non_th_school_id, school_name, city, state, zip, country, phone) 
            VALUES ('', :school_name, :city, :state, :zip, :country, :phone)"
);

// check if file uploaded and parse the csv file
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    $allowed = ['csv'];

    if (in_array($file_ext, $allowed)) {
        if ($file_error === 0) {
            $file = fopen($file_tmp, 'r');
            $success = true;
            $MASHPIA_DB->beginTransaction();
            while ($data = fgetcsv($file)) {
                $i = 0;
                $school_id = intval($data[$i++]);
                $school_name = $data[$i++];
                $city = $data[$i++];
                $state = $data[$i++];
                $zip = $data[$i++];
                $country = $data[$i++];
                $phone = $data[$i++];

                if ($school_id > 0) {
                    $result = $stmtUpdate->execute(
                        [
                            'school_id'     => $school_id,
                            'school_name'   => $school_name,
                            'city'          => $city,
                            'state'         => $state,
                            'zip'           => $zip,
                            'country'       => $country,
                            'phone'         => $phone
                        ]
                    );
                    if (!$result) {
                        $success = false;
                        $stmtUpdate->debugDumpParams();
                        break;
                    }
                } else {
                    $result = $stmtInsert->execute(
                        [
                            'school_name'   => $school_name,
                            'city'          => $city,
                            'state'         => $state,
                            'zip'           => $zip,
                            'country'       => $country,
                            'phone'         => $phone
                        ]
                    );
                    if (!$result) {
                        $success = false;
                        $stmtInsert->debugDumpParams();
                        break;
                    }
                }
            }
            if ($success) {
                $MASHPIA_DB->commit();
                echo "Successfully updated schools.";
            } else {
                $MASHPIA_DB->rollBack();
                echo "Failed to update schools.";
            }
            fclose($file);
        }
    }
}
?>
<DOCTYPE html>
<html>
<head>
    <title>Upload CSV</title>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
</body>
</html>