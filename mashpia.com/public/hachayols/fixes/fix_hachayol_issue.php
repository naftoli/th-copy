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
        }
    }
}

echo "<pre>"; 
print_r($insert); 
print_r($delete); 
print_r($info); 
echo "</pre>";

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