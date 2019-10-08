<?php
require 'db.php';
$year = 5780;

$info = [];
$sql = "select * from user_registration 
        where year = $year 
        and user_id not in (
            select user_id from registration_charges where year = $year and type = 'chayolei'
        )";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
}

foreach ( $info as $row ) {
    $amount = intval( $row['paid'] ) > 0 ? intval( $row['paid'] ) : 0;
    $sql = "insert into registration_charges 
            set user_id = " . $row['user_id'] . ", 
            school_id = " . $row['school_id'] . ", 
            type = 'chayolei', 
            amount = " . $amount . ", 
            date = '" . $row['reg_date'] . "', 
            year = " . $year;
    mysql_query( $sql ) or die( mysql_error() );
}
echo "Done.";