<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$pdo = $MASHPIA_DB;

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $success = true;
    $pdo->beginTransaction();

    if (isset($data['updates']) && is_array($data['updates'])) {
        $stmt = $pdo->prepare("
            UPDATE chidon_user_subsidies 
            SET subsidy_amount = :amount 
            WHERE chidon_user_subsidy_id = :subsidyId
        ");

        // Batch update subsidies
        foreach ($data['updates'] as $update) {
            $subsidyId = $update['subsidyId'];
            $amount = $update['amount'];
            
            $res = $stmt->execute([
                ':amount' => $amount,
                ':subsidyId' => $subsidyId
            ]);

            if (!$res) {
                $success = false;
                break;
            }
        }
    }

    if (!$success) {
        throw new Exception('Failed to update subsidies');
    }
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}