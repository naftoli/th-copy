<?php
// upload csv file to fix addresses
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$qrys = [];
if (isset($_POST['submit'])) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');
    $first = true;
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        if ($first) {
            $first = false;
            continue;
        }
        $admin_id = $data[0];
        $admin_state = $data[1];
        $admin_country = $data[2];
        $qrys[] = "UPDATE admins SET admin_state = '$admin_state', admin_country = '$admin_country' WHERE admin_id = $admin_id";
    }

    $success = true;
    $MASHPIA_DB->beginTransaction();
    foreach ($qrys as $query) {
        if (!$MASHPIA_DB->query($query)) {
            $success = false;
            break;
        }
    }
    if ($success) {
        $MASHPIA_DB->commit();
        echo "Addresses fixed.";
    } else {
        $MASHPIA_DB->rollBack();
        echo "Error fixing addresses.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8' />
    <title>Fix Admin Addresses</title>
</head>
<!-- add ability to upload file -->
<body>
<h1>Fix Admin Addresses</h1>
<?php if (!isset($_POST['submit'])) : ?>
    <form action="fix_addresses.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" />
        <input type="submit" name="submit" value="Submit" />
    </form>
<?php endif; ?>
</body>
</html>