<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if ( isset( $_FILES['schools'] ) ) {
    if ( $file = fopen($_FILES['schools']['tmp_name'], "r") ) {
        $qrys = [];
        $first = true;
        while ( $data = fgetcsv( $file ) ) {
            if ($first) {
                $first = false;
                continue;
            }
            $serial = $data[0];
            $school = $data[1];
            $users[$serial] = $school;
            $qrys[] = "update users set non_th_school = \"" . $school . "\" where user_serial = " . $serial;
        }

        $updated = 0;
        mysql_query('set autocommit=0');
        mysql_query('begin');
        $success = true;
        foreach ($qrys as $qry) {
            if (! mysql_query($qry)) {
                echo $qry . "<br />" . mysql_error();
                $success = false;
                break;
            } else $updated++;
        }
        if ($success) {
            mysql_query('commit');
            echo "Updated: " . $updated;
        }
        else mysql_query('rollback');
        mysql_query('set autocommit=1');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Add To Raffle</title>
</head>

<body>
<form action="updateSchools.php" method="post" enctype="multipart/form-data">
    <input type="file" name="schools" id="schools">
    <input type="submit" value="upload" name="submit">
</form>
</body>
</html>