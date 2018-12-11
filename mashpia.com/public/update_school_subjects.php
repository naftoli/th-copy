<?
require 'db.php';

$hschools = array(79, 198, 87, 86, 177);
$hsubjects = array();        
$sql = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC') 
        and sts.school_type_id in (12,13) 
        group by s.subject_id 
        order by s.subject_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $hsubjects[] = $row['subject_id'];
}

foreach ($hschools as $school) {
    foreach ($hsubjects as $subject) {
        $sql = "insert ignore into school_subjects values ($school, $subject)";
        //echo $sql . "<br />";
        //mysql_query($sql);
    }
}

$schools = array();
$sql = "select school_id from schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schools[] = $row['school_id'];
}

$subjects = array();        
$sql = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC') 
        and sts.school_type_id in (2,3,12,13) 
        and s.subject_id not in (27) 
        group by s.subject_id 
        order by s.subject_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $subjects[] = $row['subject_id'];
}

foreach ($schools as $school) {
    if (in_array($school, $hschools)) {
        //echo $school . "<br />";
        continue;
    }
    foreach ($subjects as $subject) {
        $sql = "insert ignore into school_subjects values ($school, $subject)";
        //echo $sql . "<br />";
        mysql_query($sql);
    }
}
