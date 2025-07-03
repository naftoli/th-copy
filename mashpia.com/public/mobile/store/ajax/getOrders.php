<?php
require '../../../db.php';
require_once '../../../api/header/db.php';
require_once '../../../class.globalSettings.php';
$user = mysql_real_escape_string($_POST['user']);

if ( !$user ) {
    echo json_encode([
        'success' => false,
        'error'   => 'No user ID provided'
    ]);
    exit;
}

$sql = "SELECT 
    up.prize_id,
    up.user_id,
    up.institution_id,
    up.quantity,
    up.status,
    up.is_reversed,
    up.created,
    up.actual_points,
    p.prize_name AS pname,
    p.points,
    p.image_id,
    dp.*
FROM
    pointsDB.user_prizes up
        LEFT JOIN
    pointsDB.prizes p USING (prize_id)
        LEFT JOIN
    deleted_prizes dp USING (prize_id)
WHERE
    up.user_id = :user 
ORDER BY
    up.created DESC";

$stmt = $MASHPIA_DB->prepare($sql);
$stmt->bindParam(':user', $user);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$info = [];
foreach ( $orders as $order ) {
    $status = $order['status'];
    if ( intval($order['is_reversed']) ) {
        $status = 'returned';
    } else if ( $status == 'Checked Out' ) {
        $status = 'ordered';
    } else if ( $status == 'Redeemed' ) {
        $status = 'redeemed';
    }
    $name = $order['pname'] ? $order['pname'] : $order['prize_name'];
    $qty = $order['quantity'];
    $date = $order['created'];
    $points = $order['points'] ? $order['points'] : $order['prize_points'];
    $actualPoints = $order['actual_points'] ? $order['actual_points'] : $points;
    $image = $order['image_id'] ? $order['image_id'] : '';
    $info[] = [
        'name' => $name,
        'qty' => $qty,
        'date' => $date,
        'status' => $status,
        'points' => $points,
        'actualPoints' => $actualPoints,
        'image' => $image
    ];
}

echo json_encode([
    'success' => true,
    'orders'  => $info
]);
