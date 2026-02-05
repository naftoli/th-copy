<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . "/../../header.php";
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

if (isset($_POST['submit'])) {
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp_name = $file['tmp_name'];
    $handle = fopen($file_tmp_name, "r");
    while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $item_id = $row[0];
        $translation = $row[1];
        $qry = "insert into chidon_items_translations (item_id, spanish) values ('$item_id', '$translation')";
        $qrys[] = $qry;
    }
    fclose($handle);
    $success = true;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    $res = mysql_query('truncate table chidon_items_translations');
    if (!$res) {
        $success = false;
        echo "error: " . mysql_error();
        mysql_query('rollback');
        exit;
    }
    foreach ($qrys as $qry) {
        if (! mysql_query($qry)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        mysql_query('commit');
        echo "done.";
    } else {
        mysql_query('rollback');
        echo "error: " . mysql_error();
    }
    mysql_query('set autocommit=1');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chidon Translations Upload</title>
</head>
<body>
    <h1>Chidon Translations Upload</h1>
    <form action="translations.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file">
        <input type="submit" value="Upload" name="submit">
    </form>
</body>
</html>