<?php
require 'db.php';

$info = array();
$sql = "select * from th_chidon where year = 5778";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $info[$row['user_id']][] = $row;
}

$duplicates = array();
foreach ($info as $id => $details) {
    $num = count($details);
    if ($num > 1) {
        for ($i = 1; $i < $num; $i++) {
            $duplicates[] = $details[$i]['th_chidon_id'];
        }
    }
}

echo "<pre>";
//print_r($info);
print_r($duplicates);
echo "</pre>";

foreach ($duplicates as $chidon_id) {
    $sql = "delete from th_chidon where th_chidon_id = " . $chidon_id;
    mysql_query( $sql );
}
echo "done";
?>