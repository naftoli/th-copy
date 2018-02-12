<?php
require 'db.php';

$source = array();
$sql = "SELECT * FROM mashpia5775.schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $source[$row['school_id']] = $row;
}

$qrys = array();
foreach ($source as $id => $school) {
    $qry = "update schools
            set chayolei = " . $school['chayolei'] . ",
            tanya = " . $school['tanya'] . ",
            tehillim = " . $school['tehillim'] . ",
            chidon = " . $school['chidon'] . "
            where school_id = " . $id;
    //$qrys[] = $qry;
    mysql_query($qry);
}
echo "Done.";
//echo "<pre>"; print_r($qrys); echo "</pre>";