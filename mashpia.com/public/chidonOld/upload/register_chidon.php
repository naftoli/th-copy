<?php
$admin_auth = ['school'];
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}
ini_set('display_errors',1);
require __DIR__ . "/../../db.php";
$qrys = [];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $serial_num = $data[0];
        $sweater = $data[1];
        $book = $data[2];
        $qry = "INSERT INTO th_chidon 
                SET
                    user_id = (select user_id from users where user_serial = $serial_num),
                    year = 5781,
                    size = '$sweater', 
                    book = $book,
                    school_id = (select school_id from users where user_serial = $serial_num), 
                    parent_id = (select admin_id from admin_auths where id = (
                        select user_id from users where user_serial = $serial_num
                    )),
                    reg_date = now()";
        $qrys[] = $qry;
    }
    fclose($handle);
}
//echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;

foreach ( $qrys as $qry ) {
    if ( !mysql_query( $qry ) ) {
        echo "There was an error - " . $qry . "<br />" . mysql_error();
        $success = false;
        break;
    }
}

if ( $success ) mysql_query('commit');
else mysql_query('rollback');
mysql_query('set autocommit=1');
echo "done.";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
        <form action="register_chidon.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <label>Upload your spreadsheet
                <br /><input type="file" name="file" class="file"></label>
            <br /><input type="submit" name="submit" value="upload" />
        </form>
    </body>
</html>