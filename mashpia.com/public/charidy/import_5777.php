<?php
require '../db.php';
$year = 5777;

$id = 206289;
$first = true;
$inserts = array();
if (($handle = fopen("charidy5777.csv", "r")) !== FALSE) {
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
                    $phone = $value;
                    break;
                case 4:
                    $donation = (int)$value;
                    break;
            }
        }
        $sql = "insert into charidy set
                charidy_id = " . $id++ . ", 
                year = " . $year . ",
                fname = \"" . $fname . "\",
                lname = \"" . $lname . "\", 
                email = \"" . $email . "\",
                donation = " . $donation . ",
                phone = '" . $phone . "'";
        //echo $sql . "<br />";
        $inserts[] = $sql;
    }
    fclose($handle);
}
//exit;
echo "<pre>";
//print_r( $inserts );
echo "</pre>";
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
