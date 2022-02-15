<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if (isset($_POST['submit'])) {
    if (isset($_FILES['user_prizes'])) {
        $qrys = [];
        $file = $_FILES['user_prizes']['tmp_name'];
        if (($handle = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $serial_num = $data[0];
                $prize = $data[1];
                $he_name = $data[2];

                if (strpos($prize, ',') !== false) {
                    $prizes = explode(',', $prize);
                } else {
                    $prizes = [$prize];
                }

                $sql = "select user_id from users where user_serial = " . $serial_num;
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $user_id = $row['user_id'];

                if ($user_id) {
                    foreach ($prizes as $prize_id) {
                        $sql = "insert into chidon_user_prizes 
                                set user_id = $user_id, 
                                year = $year, 
                                prize_id = $prize_id";
                        if ($he_name) $sql .= ", he_name = '$he_name'";
                        $qrys[] = $sql;
                    }
                } else {
                    echo "Incorrect serial number: " . $serial_num;
                    exit;
                }
            }
        }
        mysql_query('set autocommit=0');
        mysql_query("begin");
        $success = true;
        foreach ($qrys as $qry) {
            if (! mysql_query($qry)) {
                $success = false;
                break;
            }
        }
        if ($success) mysql_query("commit");
        else mysql_query("rollback");
        mysql_query("set autocommit=1");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8">
    <title>Add to user prizes</title>
</head>
<body>
    <?php if (! isset($_POST['submit'])) : ?>
    <form action="" method="post" enctype="multipart/form-data">
        Select file to upload: <input type="file" name="user_prizes" />
        <input type="submit" name="submit" value="submit">
    </form>
    <?php endif; ?>
</body>
</html>
