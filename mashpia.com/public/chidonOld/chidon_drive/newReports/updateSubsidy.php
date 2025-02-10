<?php
header('Content-Type: application/json');
require_once '../../../includes/config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $pdo->beginTransaction();

    if (isset($data['delete']) && $data['delete']) {
        // Delete subsidy
        $stmt = $pdo->prepare("DELETE FROM chidon_user_subsidies WHERE id = ?");
        $stmt->execute([$data['subsidyId']]);
    } else if (isset($data['new']) && $data['new']) {
        // Add new subsidy
        $stmt = $pdo->prepare("
            INSERT INTO chidon_user_subsidies (donation_id, user_id, amount)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$data['donationId'], $data['user_id'], $data['amount']]);
    } else {
        // Update existing subsidy
        $updates = [];
        $params = [$data['subsidyId']];

        if (isset($data['user_id'])) {
            $updates[] = "user_id = ?";
            $params[] = $data['user_id'];
        }
        if (isset($data['amount'])) {
            $updates[] = "amount = ?";
            $params[] = $data['amount'];
        }

        if (!empty($updates)) {
            $stmt = $pdo->prepare("
                UPDATE chidon_user_subsidies 
                SET " . implode(", ", $updates) . "
                WHERE id = ?
            ");
            $stmt->execute($params);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}