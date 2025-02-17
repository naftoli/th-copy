<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
reuire_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

$sql1 = "SELECT 
            cup.user_id, tci.highest_track 
        FROM
            chidon_user_prizes cup
                JOIN
            chidon_prizes cp USING (prize_id)
                JOIN 
            users u USING (user_id) 
                JOIN 
            th_chidon tc ON tc.user_id = u.user_id AND tc.year = cup.year 
                LEFT JOIN
            th_chidon_info tci ON u.user_id = tci.user_id AND tc.year = tci.year 
                JOIN 
            classes c ON u.class_id = c.class_id 
        WHERE
            cup.year = 5785 AND tc.date_paid > 0 
                AND tc.ultimate_trip = 0 AND (tci.highest_track is null OR tci.highest_track != 'yesod') 
                GROUP BY cup.user_id";

$sql2 = "SELECT 
            cup.user_id, tci.highest_track 
        FROM
            chidon_user_prizes cup
                JOIN
            chidon_prizes cp USING (prize_id)
                JOIN 
            users u USING (user_id) 
                JOIN 
            th_chidon tc ON tc.user_id = u.user_id AND tc.year = cup.year 
                LEFT JOIN
            th_chidon_info tci ON u.user_id = tci.user_id AND tc.year = tci.year 
                JOIN 
            classes c ON u.class_id = c.class_id 
        WHERE
            cup.year = 5785 AND tc.date_paid > 0 
                AND tc.ultimate_trip = 0 AND tc.user_id not in (
                    SELECT user_id FROM registration_charges WHERE year = 5785 AND type = 'RRYSD'
                )
                GROUP BY cup.user_id";

$result1 = mysql_query($sql1);
$result2 = mysql_query($sql2);

$info1 = [];
$info2 = [];
while ($row1 = mysql_fetch_assoc($result1)) {
    $info1[$row1['user_id']] = $row1['highest_track'];
}
while ($row2 = mysql_fetch_assoc($result2)) {
    $info2[$row2['user_id']] = $row2['highest_track'];  
}

// find user ids that are in info1 but not info2
$diff = array_diff(array_keys($info1), array_keys($info2));
echo "<pre>"; print_r($diff); echo "</pre>";