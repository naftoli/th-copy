<?php
ini_set('max_execution_time', 500);
$admin_auth = ['school'];
require_once 'db.php';

$info = [];
$sql = "
    select * from registration_charges 
    where year = 5780 
    and type = 'chayolei' 
    order by user_id
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[$row['user_id']][] = $row;
}

foreach ( $info as $user => $rows ) {
    if ( count( $rows ) > 1 ) {
        foreach ( $rows as $idx => $row ) {
            if ( $idx == 0 ) continue;
            try {
                $date = new DateTime( $row['date'] );
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
            $date->add( new DateInterval("P1D") );
            $sql = "update registration_charges set date = '" . $date->format('Y-m-d H:i:s') . "' where registration_charge_id = " . $row['registration_charge_id'];
            mysql_query( $sql ) or die( mysql_error() . "<br />" . $sql );
        }
    }
}
echo "done.";

// foreach ( $info as $user => $rows ) {
//     if ( count( $rows ) > 1 ) {
//         $sql = "delete from registration_charges where user_id = " . $user . " and year = 5780 and type = 'chayolei' and date != '" . $rows[0]['date'] . "'";
//         //echo $sql . "<br />";
//         mysql_query( $sql );
//     }
// }
// echo "done.";