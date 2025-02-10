<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

try {
    // Get all donations
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            chidon_donation_id,
            for_family_id, 
            donation_amount
        FROM chidon_donations 
        WHERE chidon_year = :year
    ");
    $stmt->execute([':year' => $year]);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get subsidies for each donation
    foreach ($donations as &$donation) {
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                user_id,
                subsidy_amount
            FROM chidon_user_subsidies
            WHERE chidon_donation_id = ?
        ");
        $stmt->execute([$donation['chidon_donation_id']]);
        $donation['subsidies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($donations);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}