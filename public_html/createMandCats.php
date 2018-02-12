<?php
require_once 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$info = array();
$startDate = 2458048; // October 20 2017
$sql = "select dtm.subject_id, dt.cat_ord_new, dt.cat, dtm.lang_id from date_tasks dt
        join date_tasks_missions dtm using (date_tasks_mission_id) 
        where dt.mandatory_qty = 1
        and dtm.start_date >= " . $startDate . " 
        and dt.cat_ord_new > 0 
        group by dtm.lang_id, dtm.subject_id, dt.cat_ord_new";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$qrys = array();
foreach ($info as $row) {
    $sql = "insert into mandatory_cats
            set year = " . $year . ",
            subject_id = " . $row['subject_id'] . ",
            lang_id = " . $row['lang_id'] . ",
            cat = \"" . $row['cat'] . "\"";
    $qrys[] = $sql;
}

foreach ($qrys as $qry) {
    //echo $qry . "<br />";
    mysql_query($qry);
}
echo "Done.";