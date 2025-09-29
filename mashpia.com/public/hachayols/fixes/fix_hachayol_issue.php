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

function getEligibleChildren($admin_id, $alreadyGiven) {
    global $MASHPIA_DB;

    $school_exceptions = GlobalSettings::getAustralian();
    $sql = "SELECT u.user_id, u.dob, c.class_grade FROM users u 
            JOIN classes c ON c.class_id = u.class_id 
            JOIN admin_auths aa ON u.user_id = aa.id 
            WHERE aa.admin_id = :admin_id AND aa.role_id = 1
            AND u.school_id NOT IN (" . implode(',', $school_exceptions) . ") 
            AND c.class_era = 0 
            AND u.user_id NOT IN (" . implode(',', $alreadyGiven) . ")
            ORDER BY c.class_grade DESC, u.dob DESC";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([':admin_id' => $admin_id]);
    return $stmt->fetchAll();
}

function getChildForHachayol($children) {
    foreach ($children as $child) {
        $gradeRaw = $child['class_grade'];
        $isNumeric = is_numeric($gradeRaw);
        $gradeNum = $isNumeric ? intval($gradeRaw) : null;
        if (($isNumeric && $gradeNum <= 5) || strcasecmp($gradeRaw, 'Pre1a') === 0) {
            return $child['user_id'];
        }
    }
    // If no early-grade child found, still ensure at least one per family
    return $children[0]['user_id'];
}

$sql = "SELECT 
            admin_id, user_id
        FROM
            hachayols_to_give h
                JOIN
            admin_auths aa ON aa.id = h.user_id and aa.role_id = 1 
        WHERE
            year = :year
        ORDER BY admin_id , user_id";
$stmt = $MASHPIA_DB->prepare($sql);

$sql2 = "SELECT 
            *
        FROM
            registration_charges rc
        WHERE
            year = :year AND type = 'HACH'
                AND admin_id = :admin_id";
$stmt2 = $MASHPIA_DB->prepare($sql2);

$info = [];
$stmt->execute(['year' => $year]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $admin_id = $row['admin_id'];
    $user_id = $row['user_id'];
    $info[$admin_id]['hachayols'][] = $user_id;
    $stmt2->execute(['admin_id' => $admin_id, 'year' => $year]);
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $info[$admin_id]['payments'] = count($rows2);
}

$insert = [];
$delete = [];
$qrys = [];
foreach ($info as $admin_id => $more) {
    $hachayols = $more['hachayols'];
    $numPayments = $more['payments'];
    $numHachayols = count($hachayols);
    if ($numHachayols > ($numPayments + 1)) {
        for ($i = $numPayments + 1; $i < $numHachayols; $i++) {
            $delete[] = $hachayols[$i];
            $qrys[] = "DELETE FROM hachayols_to_give WHERE user_id = " . $hachayols[$i] . " AND year = " . $year;
        }
    } else if ($numHachayols < ($numPayments + 1)) {
        for ($i = $numHachayols; $i < ($numPayments + 1); $i++) {
            $insert[] = $admin_id;
            $children = getEligibleChildren($admin_id, $more['hachayols']);
            if (! empty($children)) {
                $child = getChildForHachayol($children);
                $qrys[] = "INSERT INTO hachayols_to_give (user_id, year, admin_id) VALUES (" . $child . ", " . $year . ", " . $admin_id . ")";
            }
        }
    }
}

echo "<pre>"; 
print_r($insert); 
print_r($delete); 
print_r($qrys); 
echo "</pre>";

if (isset($_GET['fix']) && $_GET['fix'] == 1) {
    $success = true;
    $MASHPIA_DB->beginTransaction();
    foreach ($qrys as $sql) {
        $success = $MASHPIA_DB->query($sql);
        if (!$success) {
            break;
        }
    }
    if ($success) {
        $MASHPIA_DB->commit();
        echo "done";
    } else {
        $MASHPIA_DB->rollBack();
        echo "failed";
    }
}