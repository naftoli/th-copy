<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();

$ct = new ChidonTests();
$ct->setStudents();
$ct->calculateMarks();
$ct->setScores();
$ct->calculateMarks();
$all_marks = $ct->getMarks();

// qry to get all kids that should get the award
$sql = "
    SELECT
        s.school_name, u.user_id, u.class_id, u.school_id, u.user_serial, u.first_he, u.last_he, u.gender, 
        c.class_grade, c.class_sub, tc.parent_id, tc.th_chidon_id, tc.test_type, tc.reward_type 
    FROM 
        users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join th_chidon tc using (user_id) 
        join th_chidon_marks tcm using (th_chidon_id) 
    WHERE
        tc.year = $year 
    GROUP BY 
        u.user_id 
    ORDER BY
        s.school_id, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql . "<br />"; exit;
$stmt = $MASHPIA_DB->query($sql);
$rows = $stmt->fetchAll();
$info = [];
foreach ($rows as $row) {
    $row['award'] = 'cert'; // default to 'cert'
    $info[$row['school_id']][] = $row;
}

//foreach ($info as $school => &$rows) {
//    foreach ($rows as &$row) {
//        $id = $row['th_chidon_id'];
//        $marks = $all_marks[$id][1];
//        $award = 'certificate';
//        $noMarks = true;
//        foreach (['genius', 'expert', 'pro', 'maven'] as $type) {
//            // check if child got ANY mark on ANY test
//            if ($marks[$type] > 0) $noMarks = false;
//            if ($type == 'genius' && $marks[$type] >= 84) {
//                $award = ['plaque', 'trophy'];
//                $noMarks = false;
//                break;
//            }
//            else if ($type != 'maven' && $marks[$type] >= 70) {
//                $award = 'plaque';
//                $noMarks = false;
//                break;
//            }
//            else if ($marks[$type] > 0) $noMarks = false;
//        }
//        if ($noMarks) $award = 'cert';
//        $row['award'] = $award;
//    }
//}

// find out order of kids for admins
$admins = [];
$sql = "select aa.admin_id, aa.id from admin_auths aa 
        join users u on u.user_id = aa.id 
        join th_chidon tc using (user_id) 
        where tc.year = $year 
        and u.school_id in (61, 269) 
        group by id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $admins[$row['admin_id']][] = $row['id'];
}