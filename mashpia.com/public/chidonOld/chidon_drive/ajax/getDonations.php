<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

try {
    // Get all donations that have subsidies
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            cd.chidon_donation_id,
            donation_amount,
            donation_date,
            chidon_user_subsidy_id,
            user_id,
            subsidy_amount
        FROM
            chidon_donations cd
                JOIN
            chidon_user_subsidies cus ON cd.chidon_donation_id = cus.chidon_donation_id
        WHERE
            cd.chidon_year = :year
    ");
    $stmt->execute([':year' => $year]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $donations = [];
    $subsidies = [];
    foreach ($rows as $row) {
        $donation = [
            'chidon_donation_id' => $row['chidon_donation_id'],
            'donation_amount' => $row['donation_amount'],
            'donation_date' => $row['donation_date']
        ];
        if (!isset($donations[$row['chidon_donation_id']])) {
            $donations[$row['chidon_donation_id']] = $donation;
        }
        $subsidy = [
            'chidon_user_subsidy_id' => $row['chidon_user_subsidy_id'],
            'user_id' => $row['user_id'],
            'subsidy_amount' => $row['subsidy_amount'],
        ];
        $subsidies[$row['chidon_donation_id']][] = $subsidy;
    }
    echo json_encode([$donations, $subsidies]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}