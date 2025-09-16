<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        *
    FROM
        registration_charges rc
            JOIN
        users u USING (user_id)
    WHERE
        year = 5786 AND type = 'LDE'
            AND u.school_id = :school 
");

$stmtUpdate = $MASHPIA_DB->prepare("
    UPDATE registration_charges 
    SET 
        amount = :amount
    WHERE
        registration_charge_id = :id
");

$stmtInsert = $MASHPIA_DB->prepare(
    "INSERT INTO registration_charges (trans_id, user_id, school_id, admin_id, type, amount, year, discount) 
    VALUES( :trans_id, :user_id, :school_id, :admin_id, :type, :amount, :year, :discount )"
);

$success = false;
$MASHPIA_DB->beginTransaction();

$info = [];
$schools = [61, 269];
foreach ($schools as $school) {
    $stmt->execute(['school' => $school]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $info[$row['registration_charge_id']] = $row;
        $id = $row['registration_charge_id'];
        $trans_id = $row['trans_id'];
        $user_id = $row['user_id'];
        $school_id = $row['school_id'];
        $admin_id = $row['admin_id'];
        $year = $row['year'];
        $discount = $row['discount'];
        $amount = intval($row['amount']);

        if ($school == 61) {
            $amount -= 10;
            $types = ['MYSLDS-10'];
        } else if ($school == 269) {
            $amount -= 30;
            $types = ['AKLDS-10', 'AKLDBC-20'];
        }
        $res = $stmtUpdate->execute(['id' => $id, 'amount' => $amount]);
        $stmtUpdate->debugDumpParams();
        echo "<br />";
        if (!$res) {
            $success = false;
            break;
        }
        foreach ($types as $type) {
            $res = $stmtInsert->execute([
                'trans_id' => $trans_id, 
                'user_id' => $user_id, 
                'school_id' => $school_id, 
                'admin_id' => $admin_id, 
                'type' => $type, 
                'amount' => $amount, 
                'year' => $year, 
                'discount' => $discount]);
            $stmtInsert->debugDumpParams();
            echo "<br /><br />";
            if (!$res) {
                $success = false;
                break;
            }
        }
    }
}

$success = false;
if ($success) {
    $MASHPIA_DB->commit();
    echo "success";
} else {
    $MASHPIA_DB->rollBack();
    echo "error";
}
echo "<pre>"; print_r($info); echo "</pre>";
?>