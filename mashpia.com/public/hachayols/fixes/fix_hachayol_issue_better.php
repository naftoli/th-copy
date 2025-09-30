<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        aa.admin_id, u.user_registered, u.user_id, c.class_grade, h.hachayol_id
    FROM
        users u
            JOIN
        user_registration ur USING (user_id)
            JOIN
        admin_auths aa ON aa.id = u.user_id
            JOIN
        classes c USING (class_id)
            LEFT JOIN
        hachayols_to_give h USING (user_id, year)
    WHERE
        ur.year = :year
    GROUP BY aa.admin_id
    ORDER BY aa.admin_id, class_grade
");
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($rows);
echo "</pre>";