<?php
require '../../db.php';
$year = 5777;

$first = true;
$info = array();
if (($handle = fopen("donations5777.csv", "r")) !== FALSE) {
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
                    $fname = $value;
                    break;
                case 1:
                    $lname = $value;
                    break;
                case 2:
                    $email = $value;
                    break;
                case 3:
                    $address = $value;
                    break;
                case 4:
                    $city = $value;
                    break;
                case 5:
                    $zip = $value;
                    break;
                case 6:
                    $state = $value;
                    break;
                case 7:
                    $country = $value;
                    break;
                case 8:
                    $phone = $value;
                    break;
                case 9:
                    $donation = (int)$value;
                    break;
            }
        }
        $info[] = array(
            'first'     =>  $fname,
            'last'      =>  $lname,
            'email'     =>  $email,
            'address'   =>  $address,
            'city'      =>  $city,
            'state'     =>  $state,
            'zip'       =>  $zip,
            'country'   =>  $country,
            'phone'     =>  $phone,
            'amount'    =>  $donation
        );
    }
    fclose($handle);
}
//echo "<pre>"; print_r( $info ); echo "</pre>";

foreach ($info as $row) {
    $email = $row['email'];
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        // find out if donor exists
        $sql = "select * from charidy_donors where email = '" . $email . "'";
        $result = mysql_query( $sql );
        if (mysql_num_rows( $result ) == 0) {
            $sql = "insert into charidy_donors set
                    first_name = \"" . $row['first'] . "\",
                    last_name = \"" . $row['last'] . "\",
                    email = '" . $email . "',
                    address = '" . $row['address'] . "',
                    city = '" . $row['city'] . "',
                    state = '" . $row['state'] . "',
                    zip = '" . $row['zip'] . "',
                    country = '" . $row['country'] . "',
                    phone = '" . $row['phone'] . "',
                    needs_call = 1";
            //echo $sql . "<br />";
            //$donor_id = 23444;
            mysql_query( $sql ) or die( mysql_error() );
            $donor_id = mysql_insert_id();
        } else {
            $donorRow = mysql_fetch_assoc( $result );
            $donor_id = $donorRow['donor_id'];
        }
        $sql = "insert into charidy_donations
                set donor_id = " . $donor_id . ",
                amount = " . $row['amount'] . ",
                year = " . $year;
        //echo $sql . "<br /><br />";
        mysql_query( $sql ) or die( mysql_error() );
    }
}
echo "done.";