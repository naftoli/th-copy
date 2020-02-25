<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "select * from th_chidon";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['year']][$row['school_id']][] = $row['user_id'];
}

$duplicates = [];
foreach ( $info as $year => $more ) {
    foreach ( $more as $school => $other ) {
        if ( count( $other ) > 1 ) {
            $duplicates[$year][] = $other[0];
        }
    }
}

echo "<pre>"; print_r( $duplicates ); echo "</pre>";