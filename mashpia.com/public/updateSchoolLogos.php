<?php
$admin_auth = ['school'];
require_once 'header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "Access Denied.";
    exit;
}

if ( isset( $_POST['submit'] ) ) {
    //echo "<pre>"; print_r( $_FILES ); echo "</pre>"; 
    foreach ( $_FILES as $school => $file ) {
        //echo $school . "<br />";
        //echo "<pre>"; print_r( $file ); echo "</pre>";
        $info = explode('-', $school);
        $type = $info[0];
        $id = $info[1];
        $target_file = "schoolLogos/" . $file['name'];
        if ( move_uploaded_file( $file['tmp_name'], $target_file ) ) {
            $field = 'logo_' . $type;
            $sql = "update schools set " . $field . " = \"" . addslashes( $file['name'] ) . "\" where school_id = " . $id;
            //echo $sql;
            mysql_query( $sql ) or die( mysql_error() );
        }
    }
}

$schools = [];
$sql = "select * from schools where chayolei = 1 and test_school = 0";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $schools[$row['school_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            body {
                font-family: Arial;
                font-size: 14px;
            }
            img {
                max-width: 150px;
            }
            hr {
                margin-top: 20px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <form action="updateSchoolLogos.php" method="post" enctype="multipart/form-data">
            <br /><input type='submit' name='submit' value='Change Logo(s)' /><br />
            <hr />
            <?php foreach ( $schools as $id => $school ) : ?>
                School Name: <?= $school['school_name'] ?><br /><br />
                Boys Logo: <img src="schoolLogos/<?= $school['logo_boys'] ?>" /><br />
                <input type='file' name="boys-<?= $id ?>" /><br /><br />
                Girls Logo: <img src="schoolLogos/<?= $school['logo_girls'] ?>" /><br />
                <input type='file' name="girls-<?= $id ?>" /><br /><br />
                <hr />
            <?php endforeach; ?>
            <input type='submit' name='submit' value='Change Logo(s)' />
        </form>
    </body>
</html>