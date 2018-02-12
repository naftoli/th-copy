<?php
require 'db.php';

$qrys = array();
if (($info = fopen("schoolPrincipals.csv", "r")) !== false) {
	while (($data = fgetcsv($info, 1000, ",")) !== false) {
        $school = $data[0];
        $principal = $data[1];
        
        $qrys[] = "update schools
                    set principal = '" . $principal . "' 
					where school_id = " . $school;
    }
}

$updated = 0;
foreach ($qrys as $qry) {
    if (mysql_query($qry)) $updated++;
	else echo mysql_error() . "<br />";
}
echo "Updated: " . $updated;