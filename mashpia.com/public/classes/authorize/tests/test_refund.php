<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../Refund.php';
require_once __DIR__ . '/../CustomerProfile.php';
require_once __DIR__ . '/../Auth.php';

use classes\authorize\Refund;
use classes\authorize\CustomerProfile;
use classes\authorize\Auth;

try {
    echo "Processing refund...<br />";
    // Full refund using your database transaction ID
    $result = Refund::processRefundFromDatabase(1, null, $MASHPIA_DB, false, 121202089662, false);

    // Partial refund
    $result = Refund::processRefundFromDatabase(1, 2.5, $MASHPIA_DB, false, 121202089662, false);
    echo "Refund processing completed.<br />";

    if ($result['success']) {
        echo "✅ Refund successful!<br />";
        echo "Database Transaction ID: " . $result['db_transaction_id'] . "<br />";
        echo "Original Authorize.Net Transaction ID: " . $result['original_authorize_trans_id'] . "<br />";
        echo "Refund Transaction ID: " . $result['refund_transaction_id'] . "<br />";
        echo "Amount Refunded: $" . number_format($result['refund_amount'], 2) . "<br />";
        echo "Original Amount: $" . number_format($result['original_amount'], 2) . "<br />";
        echo "Used JSON card data: " . ($result['used_json_data'] ? 'Yes' : 'No') . "<br />";
        echo "Message: " . $result['message'] . "<br />";
    } else {
        echo "❌ Refund failed: " . $result['error'] . "<br />";
        if (isset($result['authorize_trans_id'])) {
            echo "Authorize.Net Transaction ID: " . $result['authorize_trans_id'] . "<br />";
        }
        if (isset($result['error_code'])) {
            echo "Error Code: " . $result['error_code'] . "<br />";
        }
    }

    echo "<pre>"; print_r($result); echo "</pre>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br />";
    echo "Stack trace:<br />" . $e->getTraceAsString() . "<br />";
}