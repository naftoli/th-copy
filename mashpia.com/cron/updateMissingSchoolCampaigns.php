<?php
require_once '../db.php';

// find all schools that don't have campaigns for all subjects
$ids = [];
$sql = "select school_id from schools where school_era is null and school_id not in (select school_id from school_subjects)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ids[] = $row['school_id'];
}

$campaigns = [];
$sql = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC', 'Tanya', 'Hakhel') 
        and sts.school_type_id in (2,3,4,5,12,13) 
        group by s.subject_id 
        order by s.subject_name";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
    $campaigns[] = $row['subject_id'];
}
//print_r($campaigns);

foreach ($ids as $id) {
    foreach ($campaigns as $campaign) {
        $sql = "insert ignore into school_subjects values($id, $campaign)";
        mysql_query($sql) or die(mysql_error());
    }
}