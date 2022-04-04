<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . "class.globalSettings.php";
$year = GlobalSettings::getChidonYear();
$year--;

if (isset($_POST['submit'])) {
    $qrys = [];
    $file = $_FILES['file']['tmp_name'];
    if (($handle = fopen($file, "r")) !== false) {
        $first = true; // skip first row
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($first) {
                $first = false;
                continue;
            }
            $serial = $data[0];
            $qrys[] = "update th_chidon set surprise_gift = 1 where year = " . $year . " and user_id = (
                        select user_id from users where user_serial = " . $serial . ")";
        }
        fclose($handle);
    }

    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) mysql_query('commit');
    else {
        echo mysql_error();
        mysql_query('rollback');
    }
    mysql_query('set autocommit=1');
    echo "done.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<form action="updates.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
    <label>Upload your file
        <br /><input type="file" name="file" class="file"></label>
    <br /><input type="submit" name="submit" value="upload" />
</form>
</body>
</html>
