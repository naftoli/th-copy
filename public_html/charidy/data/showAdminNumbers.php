<?php
require '../../db.php';

$info = array();
$sql = "select * from admins";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[] = $row;
}

$updates = array();
foreach ($info as $row) {
    $phones = array(
        'phone1'    =>  'admin_phone_home',
        'phone2'    =>  'admin_phone_work',
        'phone3'    =>  'admin_phone_mobile',
        'phone4'    =>  'admin_phone_mobile2'
    );
    foreach ($phones as $newPhone => $phone) {
        $number = filter_var($row[$phone], FILTER_SANITIZE_NUMBER_INT);
        $number = str_replace('+', '', $number);
        $number = str_replace('-', '', $number);
        if ($number) {
            $updates[] = "update admins
                          set " . $newPhone . " = " . $number . "
                          where admin_id = " . $row['admin_id'];
        }
    }
}
//echo "<pre>"; print_r( $updates ); echo "</pre>";
foreach ($updates as $update) {
    mysql_query($update);
}
echo "done.";