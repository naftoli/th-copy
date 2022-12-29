<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require '../header.php';
require '../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "no permission.";
    exit;
}

$sql = "SELECT 
            COUNT(*) AS num_missions, user_id, mission_name, lang_id
        FROM
            birthdays b
                JOIN
            date_tasks_missions dtm USING (date_tasks_mission_id)
        WHERE
            dtm.start_date > 2459944
        GROUP BY user_id , lang_id
        HAVING num_missions > 1
        ORDER BY user_id, lang_id";
$result = $mysqli->query($sql);
$info = $result->fetch_all(MYSQLI_ASSOC);

$missions = [];
$stmt = $mysqli->prepare("
SELECT 
    user_id, date_tasks_mission_id, lang_id
FROM
    birthdays b
        JOIN
    date_tasks_missions dtm USING (date_tasks_mission_id)
WHERE
    user_id = ?"
);
foreach ($info as $row) {
    print_r($row);
    $stmt->bind_param("i", $row['user_id']);
    $stmt->execute();
    while ($row = $stmt->fetch_assoc()) {
        $missions[$row['user_id']][$row['lang_id']][] = $row['date_tasks_mission_id'];
    }
}

echo "<pre>"; print_r($missions); echo "</pre>";