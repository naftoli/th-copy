<?php
require '../../db.php';
$year = 5778;

$first = true;
$info = array();
if (($handle = fopen("donations5778.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        if ($first) {
            $first = false;
            continue;
        }
        $num = count($data);
        for ($c = 0; $c < $num; $c++) {
            $value = trim( $data[$c] );
            switch ( $c ) {
                case 0:
                    $charidy_id = $value;
                    break;
                case 1:
                    $email = $value;
                    break;
                case 2:
                    $name = $value;
                    break;
                case 3:
                    $address1 = $value;
                    break;
                case 4:
                    $address2 = $value;
                    break;
                case 5:
                    $city = $value;
                    break;
                case 6:
                    $zip = $value;
                    break;
                case 7:
                    $country = $value;
                    break;
                case 8:
                    $state = $value;
                    break;
                case 9:
                    $phone = $value;
                    break;
                case 10:
                    $matched = (int)$value;
                    break;
                case 11:
                    $donation = (int)$value;
                    break;
                case 12:
                    $date = $value;
                    break;
                case 13:
                    $ip = $value;
                    break;
                case 14:
                    $comment = $value;
                    break;
                case 15:
                    $relation_id = (int)$value;
                    break;
            }
        }
        $info[] = array(
            'name'      =>  $name,
            'email'     =>  $email,
            'address1'  =>  $address,
            'address2'  =>  $address2,
            'city'      =>  $city,
            'state'     =>  $state,
            'zip'       =>  $zip,
            'country'   =>  $country,
            'phone'     =>  $phone,
            'amount'    =>  $donation, 
            'comment'   =>  $comment, 
            'ip'        =>  $ip, 
            'relation'  =>  $relation_id, 
            'date'      =>  $date, 
            'matched'   =>  $matched, 
            'id'        =>  $charidy_id
        );
    }
    fclose($handle);
}
//echo "<pre>"; print_r( $info ); echo "</pre>";

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ($info as $row) {
    $sql = "insert into charidy_temp_donations 
            set donation_id = " . $row['id'] . ", 
            name = '" . addslashes( $row['name'] ) . "', 
            address1 = '" . addslashes( $row['address1'] ) . "', 
            address2 = '" . addslashes( $row['address2'] ) . "', 
            city = '" . $row['city'] . "', 
            state = '" . $row['state'] . "', 
            zip = '" . $row['zip'] . "', 
            country = '" . $row['country'] . "', 
            phone = '" . $row['phone'] . "', 
            donation_amount = " . $row['amount'] . ", 
            with_matching = " . $row['matched'] . ", 
            donation_date = '" . $row['date'] . "',
            ip = '" . $row['ip'] . "', 
            email = '" . $row['email'] . "', 
            comment = '" . addslashes( $row['comment'] ) . "', 
            relation_id = " . $row['relation'] . ", 
            year = 5778";
    //echo $sql . "<br />";
    if (!mysql_query($sql)) {
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query('commit');
    echo "done.";
} else {
    mysql_query('rollback');
    echo $sql . "<br />" . mysql_error();
}
mysql_query('set autocimmit=1');