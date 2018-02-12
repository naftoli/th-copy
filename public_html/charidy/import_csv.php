<?php
require '../db.php';
$year = 5776;

$first = true;
$inserts = array();
if (($handle = fopen("charidy.csv", "r")) !== FALSE) {
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
                    $id = $value;
                    break;
                case 1:
                    $fname = $value;
                    break;
                case 2:
                    $lname = $value;
                    break;
                case 3:
                    $comment = $value;
                    break;
                case 4:
                    $email = $value;
                    break;
                case 5:
                    $address = $value;
                    break;
                case 6:
                    $city = $value;
                    break;
                case 7:
                    $zip = $value;
                    break;
                case 8:
                    $state = $value;
                    break;
                case 9:
                    $country = $value;
                    break;
                case 10:
                    $phone = $value;
                    break;
                case 11:
                    $withMatching = $value;
                    break;
                case 12:
                    $donation = $value;
                    break;
                case 13:
                    $donation_date = $value;
                    break;
                case 14:
                    $solicited_by = $value;
                    break;
            }
        }
        $sql = "insert into charidy set
                charidy_id = " . $id . ",
                year = " . $year . ",
                fname = \"" . $fname . "\",
                lname = \"" . $lname . "\", 
                email = \"" . $email . "\",
                address = \"" . $address . "\",
                city = '" . $city . "',
                zip = '" . $zip . "',
                state = '" . $state . "',
                country = '" . $country . "',
                donation = " . $donation . ",
                with_matching = " . $withMatching . ",
                donation_date = '" . $donation_date . "',
                solicited_by = '" . $solicited_by . "',
                donor_comment = \"" . $comment . "\",
                phone = '" . $phone . "'";
        //echo $sql . "<br />";
        $inserts[] = $sql;
    }
    fclose($handle);
}
//exit;
$success = true;
mysql_query("set_autocommit=0");
mysql_query("begin");
foreach ($inserts as $sql) {
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit=1");
    echo "success";
} else {
    mysql_query("rollback");
    mysql_query("set autocommit=1");
    echo "there were errors";
}
