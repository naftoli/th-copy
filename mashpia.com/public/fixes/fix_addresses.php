<?php
// upload csv file to fix addresses
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('No Permission');
}

$stmt = $MASHPIA_DB->prepare("UPDATE admins SET admin_state = :state, admin_country = :country WHERE admin_id = :id");
if (isset($_POST['submit'])) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, 'r');
    $first = true;
    $success = true;
    $MASHPIA_DB->beginTransaction();

    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        if ($first) {
            $first = false;
            continue;
        }
        $admin_id = $data[0];
        $admin_state = $data[1];
        $admin_country = $data[2];
        $result = $stmt->execute([
            ':state' => $admin_state,
            ':country' => $admin_country,
            ':id' => $admin_id
        ]);
        if (!$result) {
            $success = false;
            break;
        }
    }
    fclose($handle);
    if ($success) {
        $MASHPIA_DB->commit();
        echo "Addresses fixed.";
    } else {
        $MASHPIA_DB->rollBack();
        $stmt->debugDumpParams();
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