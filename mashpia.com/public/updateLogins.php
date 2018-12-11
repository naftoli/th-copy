<?php
require 'db.php';

$classes = array();
$sql = "select * from classes c 
        join schools s using (school_id)
        where c.class_era = 5776
        and s.school_initials is not null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $classes[$row['school_id']] = $row;
}

$errors = array();
foreach ($classes as $school => $info) {
    foreach ($info as $grade) {
        $sql = "select * from classes
                where class_grade = '" . $grade['class_grade'] . "
                and class_sub = '" . $grade['class_sub'] . " 
                and school_id = " . $school . "
                and class_era = 0";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $oldID = $grade['class_id'];
            $newID = $row['class_id'];
            
            $sql = "update admin_auths
                    set id = " . $newID ."
                    where id = " . $oldID . "
                    and auth = 'class'
                    and role_id = 13";
            mysql_query($sql);
        } else {
            $errors[] = "Classes missing in " . $school . "-" . $grade['class_grade'] . "-" . $grade['class_sub'];    
        }
    }
}
echo 'Done';