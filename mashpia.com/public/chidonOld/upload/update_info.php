<?php
$admin_auth = ['school'];
require_once __DIR__ . "/../../header.php";
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
    </head>
    <body>
<?php
// ini_set('display_errors',1);
require __DIR__ . "/../../class.globalSettings.php";
$year = GlobalSettings::getChidonYear();

$info = [];
if (($handle = fopen("chidon_updates.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $info[] = $data;
    }
    fclose($handle);
}
// echo "<pre>"; print_r( $info ); echo "</pre>";

// find out user ids update hebrew names
$qrys = [];
foreach ( $info as $data ) {
    if ( intval($data[0]) ) {
        $sql = "select user_id from th_chidon where th_chidon_id = " . intval($data[0]);
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        if ( $row['user_id'] ) {
            $qrys[] = "update users set first_he = \"" . $data[1] . "\", last_he = \"" . $data[2] . "\" where user_id = " . intval($row['user_id']);
        }
        $qrys[] = "update th_chidon set host = \"" . $data[3] . "\", host_number = '" . $data[4] . "' where th_chidon_id = " . intval($data[0]);
    }
}

// echo "<pre>"; print_r( $qrys ); echo "</pre>"; exit;
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

if ( $success ) {
    mysql_query('commit');
    echo "done.";
} else {
    mysql_query('rollback');
    echo "Errors.";
}
mysql_query('set autocommit=1');

echo "</body></html>";