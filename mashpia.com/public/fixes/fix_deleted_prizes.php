<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to view this page.";
    exit;
}

$sql = "SELECT * FROM pointsDB.user_prizes WHERE institution_id = 585 AND is_reversed = 1 AND modified >= '2022-01-28'";
$redeemed = $POINTS_DB->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$stockStmt = $POINTS_DB->prepare(
    'UPDATE pointsDB.prizes SET prize_count = prize_count - ? WHERE prize_id = ?'
);

$prizeStmt = $POINTS_DB->prepare(
    'UPDATE pointsDB.user_prizes SET is_reversed = 0, reversed_by = NULL, redeemed_by = ?, status = \'Redeemed\' WHERE user_prize_id = ?'
);

$pointsStmt = $POINTS_DB->prepare(
    'DELETE FROM pointsDB.user_points WHERE prize_id = ? AND user_prize_id = ? AND user_id = ? 
               AND resource_name = \'transaction_manager_store\' AND reversed_user_point_id IS NOT NULL'
);

$success = true;
$POINTS_DB->beginTransaction();

// reverse redeemed orders
foreach ($redeemed as $order) {
    // update stock
    if (! $stockStmt->execute([$order['quantity'], $order['prize_id']])) {
        echo "Failed to update stock for prize_id: {$order['prize_id']}";
        $success = false;
        break;
    }
    // update user_prizes
    if (! $prizeStmt->execute([$order['reversed_by'], $order['user_prize_id']])) {
        echo "Failed to update user_prizes for user_prize_id: {$order['user_prize_id']}";
        $success = false;
        break;
    }
    // delete reversed user_points
    if (! $pointsStmt->execute([$order['prize_id'], $order['user_prize_id'], $order['user_id']])) {
        echo "Failed to delete user_points for user_prize_id: {$order['user_prize_id']}";
        $success = false;
        break;
    }
}

if ($success) {
    $POINTS_DB->commit();
    echo "Successfully reversed orders.";
} else {
    $POINTS_DB->rollBack();
    echo "Failed to reverse orders.";
}
