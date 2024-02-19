<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

function subtractForPrizes()
{
    global $MASHPIA_DB;

    $cheshbon = [];
    $individual_amounts = [
        75 => [284, 285, 286, 287, 288, 289, 290, 291, 295, 302, 303],
        35 => [395],
        30 => [380, 381]
    ];

    $stmt = $MASHPIA_DB->prepare("
            SELECT 
                *
            FROM
                chidon_user_prizes
            WHERE
                year = :year"
    );
    $res = $stmt->execute([
        ':year' => 5784
    ]);
    if ($res) {
        $prizes = $stmt->fetchAll();
        foreach ($prizes as $prize) {
            $total = 0;
            if (!empty($prize['he_name'])) {
                foreach ($individual_amounts as $amount => $prize_ids) {
                    if (in_array($prize['prize_id'], $prize_ids)) {
                        $total += $amount;
                    }
                }
                $cheshbon[$prize['user_id']][] = $total;
            }
        }
    }
    return $cheshbon;
}

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon SET prepaid_credit = :amount where year = :year and user_id = :user
");

$totals = [];
$data = subtractForPrizes();
foreach ($data as $user_id => $amounts) {
    foreach ($amounts as $idx => $amount) {
        if ($idx == 0) $totals[$user_id] = $amount;
        else $totals[$user_id] += $amount;
    }
}

foreach ($totals as $user_id => $amount) {
//    echo "Updating user $user_id with $amount<br />";
//    continue;
    if (! $stmt->execute([
        ':amount' => $amount,
        ':year' => 5784,
        ':user' => $user_id
    ])) {
        echo "Error updating user $user_id\n";
        exit;
    }
}
echo "Done\n";