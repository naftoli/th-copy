<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('You are not authorized to access this page.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['file']['tmp_name'];
        // open the file
        $handle = fopen($file, "r");
        // read the 1st row as headings
        $header = fgetcsv($handle);
        // prepare the insert statement
        $stmt = $MASHPIA_DB->prepare('
            INSERT INTO posters (school_id, chayolei_b, chayolei_g, chidon_b, chidon_g, chayolei_bg, chidon_bg) 
            VALUES ((SELECT school_id FROM schools WHERE school_number = :school), :chayolei_b, :chayolei_g, :chidon_b, :chidon_g, :chayolei_bg, :chidon_bg)');
        // read each data row
        while (($data = fgetcsv($handle)) !== FALSE) {
            $school = $data[0];
            $chayolei_b = $data[1];
            $chayolei_g = $data[2];
            $chidon_b = $data[3];
            $chidon_g = $data[4];
            $chayolei_bg = $data[5];
            $chidon_bg = $data[6];
            // insert the data into the database
            $stmt->execute([
                'school' => $school,
                'chayolei_b' => $chayolei_b,
                'chayolei_g' => $chayolei_g,
                'chidon_b' => $chidon_b,
                'chidon_g' => $chidon_g,
                'chayolei_bg' => $chayolei_bg,
                'chidon_bg' => $chidon_bg
            ]);
        }
    } else {
        echo 'Error uploading file';
    }
    echo 'Data uploaded successfully';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Posters</title>
</head>
<body>
    <h1>Upload Poster Amount</h1>
    <!-- Form to upload CSV file -->
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept=".csv">
        <input type="submit" value="Upload">
    </form>
</body>