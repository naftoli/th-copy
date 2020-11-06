<?php
$admin_auth = ['school'];
require __DIR__ . "/../../header.php";
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}
ini_set('display_errors',1);
$year = 5781;
$qrys = [];
if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $chidon_id = $data[0];
        $sweater = strtolower($data[1]);
        $book = $data[2];
        // get info using chidon_id
        $sql = "select user_id from th_chidon where th_chidon_id = " . $chidon_id;
        $result = mysql_query($sql);
        if ($row = mysql_fetch_assoc($result)) {
            $user_id = $row['user_id'];
            $qry = "INSERT INTO th_chidon 
                    SET
                        user_id = $user_id,
                        year = $year,
                        size = '$sweater', 
                        book = $book,
                        school_id = (select school_id from users where user_id = $user_id), 
                        parent_id = (select admin_id from admin_auths where id = $user_id),
                        reg_date = now()";
            $qrys[] = $qry;
        } else {
            echo "couldn't find chidon id";
            exit;
        }
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