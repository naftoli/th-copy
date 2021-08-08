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
    if (move_uploaded_file($file, "regInfoExtra.csv")) {
        if (($handle = fopen('regInfoExtra.csv', "r")) !== FALSE) {
            $qrys = [];
            $first = true;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($first) {
                    $first = false;
                    continue;
                }
                $i = 0;
                $admin_id = $data[$i++];
                $extra = intval($data[$i++]) ?? 0;
                $qry = "insert into th_chidon_zelda_extra 
                        set admin_id = $admin_id, 
                        extra = $extra";
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
