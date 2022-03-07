<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if ( isset( $_FILES['schools'] ) ) {
    if ( $file = fopen($_FILES['schools']['tmp_name'], "r") ) {
        $users = [];
        $first = true;
        while ( $data = fgetcsv( $file ) ) {
            if ($first) {
                $first = false;
                continue;
            }
            $serial = $data[0];
            $city = $data[1];
            $state = $data[2];
            $school = $data[3];

            // find out user id from serial
            $sql = "select user_id, school_id from users where user_serial = " . $serial;
            $result = mysql_query( $sql );
            $row = mysql_fetch_assoc( $result );
            $user_id = $row['user_id'];
            $school_id = $row['school_id'];

            // only save myshliach / anashkinder kids
            if (in_array($school_id, [61, 269])) {
                $users[] = [
                    'id' => $user_id,
                    'city' => $city,
                    'state' => $state,
                    'school' => $school,
                    'school_id' => $school_id
                ];
            }
        }
        $qrys = [];
        foreach ($users as $user) {
            $qrys[] = "update users set non_th_city = '" . $user['city'] . "', non_th_state = '" . $user['state'] . "' 
                    where user_id = " . $user_id;
        }
//        echo "<pre>"; print_r($qrys); echo "</pre>";
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