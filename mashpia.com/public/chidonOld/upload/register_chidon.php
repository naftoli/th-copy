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
if (isset($_FILES['file'])) {
    if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $serial = $data[0];
            $sweater = strtolower($data[1]);
            $book = $data[2];
            if ($serial) {
                // get some details
                $sql = "select school_id, admin_id, user_id from users u 
                        join admin_auths aa on aa.id = u.user_id 
                        where u.user_serial = " . $serial;
                $result = mysql_query($sql);
                if ($row = mysql_fetch_assoc($result)) {
                    $user_id = $row['user_id'];
                    $school_id = $row['school_id'];
                    $admin_id = $row['admin_id'];
                    $qry = "INSERT IGNORE INTO th_chidon 
                            SET
                                user_id = $user_id,
                                year = $year,
                                size = '$sweater', 
                                book = $book,
                                school_id = $school_id, 
                                parent_id = $admin_id,
                                reg_date = now()";
                    $qrys[] = $qry;
                    $reg_qry = "
                    INSERT IGNORE INTO registration_charges
                    SET
                        trans_id = 0, 
                        user_id = $user_id, 
                        school_id = $school_id, 
                        amount = 10, 
                        year = $year,
                        date = now(), 
                        type = 'chidon'";
                    $qrys[] = $reg_qry;
                }
            }
        }
        fclose($handle);
    }
    //echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
    mysql_query('set autocommit=0');
    mysql_query('begin');
    $success = true;

    $inserted = 0;
    foreach ($qrys as $qry) {
        if (!mysql_query($qry)) {
            echo "There was an error - " . $qry . "<br />" . mysql_error();
            $success = false;
            break;
        } else $inserted++;
    }

    if ($success) mysql_query('commit');
    else mysql_query('rollback');
    mysql_query('set autocommit=1');
    echo "Inserted: " . $inserted;
}
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