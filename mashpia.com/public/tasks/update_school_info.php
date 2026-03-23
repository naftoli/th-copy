<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$sql1 = "
    update user_registration ur 
    join users u using (user_id) 
    set ur.school_id = u.school_id 
    where ur.year = $year 
    and ur.school_id != u.school_id
";

$sql2 = "
    update th_chidon ur 
    join users u using (user_id) 
    set ur.school_id = u.school_id 
    where ur.year = $year 
    and ur.school_id != u.school_id
";

$sql3 = "
    update registration_charges ur 
    join users u using (user_id) 
    set ur.school_id = u.school_id 
    where ur.year = $year 
    and ur.school_id != u.school_id
";

mysql_query( $sql1 );
mysql_query( $sql2 );
mysql_query( $sql3 );

echo "done.";