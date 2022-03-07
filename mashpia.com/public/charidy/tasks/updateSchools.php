<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

echo "<pre>"; print_r($_FILES); echo "</pre>";

if ( isset( $_FILES['schools'] ) ) {
    if ( $file = fopen($_FILES['schools']['tmp_name'], "r") ) {
        $users = [];
        while ( $data = fgetcsv( $file ) ) {
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

            $users[] = [
                'id'    => $user_id,
                'city'  => $city,
                'state' => $state,
                'school'    => $school,
                'school_id' => $school_id
            ];
        }
        echo "<pre>"; print_r($users); echo "</pre>";
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