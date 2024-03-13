<?php
$admin_auth = ['school'];
require '../header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_FILES['file'])) {
    // parse file as csv file
    $qrys = [];
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");
    $firstRow = true;
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($firstRow) {
            $firstRow = false;
            continue;
        }
        $serial = $row[0];
        $color = $row[1];
        $code = $row[2];
        $sql = "insert into chidon_lanyards (user_serial, color, code, year) values ('$serial', '$color', \"$code\", '$year')";
        $qrys[] = $sql;
    }
    fclose($handle);

    $success = true;
    mysql_query("set autocommit=0");
    mysql_query("start transaction");
    foreach ($qrys as $sql) {
        if (! mysql_query($sql)) {
            $success = false;
            echo $sql . "<br />Error: " . mysql_error();
            break ;
        }
    }
    if ($success) {
        mysql_query("commit");
        echo "Lanyards added successfully.";
    } else {
        mysql_query("rollback");
    }
    mysql_query("set autocommit=1");
}
?>
<DOCTYPE html>
<html>
<head>
    <title>Add to Lanyards</title>
</head>
<!--  create from to upload file  -->
<body>
    <form action="upload_lanyards.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>