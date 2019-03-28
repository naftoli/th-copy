<?php
require '../../db.php';
$year = 5778;

$first = true;
$inserts = array();
if (($handle = fopen("charidy_donation_info_5778.csv", "r")) !== FALSE) {
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
                    $json = $value;
                    break;
            }
        }
        $inserts[] = "insert into charidy_temp_data set id = " . $id . ", data = '" . addslashes( $json ) . "'";
    }
} 
//echo "<pre>"; print_r( $inserts ); echo "</pre>"; exit;
foreach ($inserts as $sql) {
    mysql_query( $sql ) or die( mysql_error() );
}
echo "done.";
?>