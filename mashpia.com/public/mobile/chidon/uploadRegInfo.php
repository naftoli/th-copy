<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

if (isset($_POST['submit'])) {
    $file = $_FILES['regInfo']['tmp_name'];
    if (move_uploaded_file($file, "regInfo.csv")) {
        if (($handle = fopen('regInfo.csv', "r")) !== FALSE) {
            $qrys = [];
            $first = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($first) {
                    $first = false;
                    continue;
                }
                $i = 0;
                $admin_id = $data[$i++];
                $chidon_id = $data[$i++];
                $reg_fee = $data[$i++];
                $chidon_drive = $data[$i++];
                $subsidy = $data[$i++];
                $coupon = $data[$i++];
                $coupon_reason = $data[$i++];
                $paid = $data[$i++];
                if (empty($paid)) $paid = 0;
                $balance = $data[$i++];
                if (empty($balance)) $balance = 0;
                $qry = "insert into th_chidon_zelda 
                        set th_chidon_id = $chidon_id, 
                        admin_id = $admin_id, 
                        reg_fee = $reg_fee, 
                        chidon_drive = $chidon_drive, 
                        subsidy = $subsidy, 
                        coupon = '$coupon', 
                        coupon_reason = '$coupon_reason', 
                        paid = $paid, 
                        balance = $balance";
//                echo $qry;
                $qrys[] = $qry;
            }
            fclose($handle);
            foreach ($qrys as $qry) mysql_query($qry) or die(mysql_error() . "<br />" . $qry);
            echo "done.";
        }
    } else {
        echo "Error saving file.";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Upload Reg Info</title>
    </head>
    <body>
        <form action="" method="post" enctype="multipart/form-data">
            Select file to upload: <input type="file" name="regInfo" />
            <br /><input type="submit" name="submit" value="upload" />
        </form>
    </body>
</html>
