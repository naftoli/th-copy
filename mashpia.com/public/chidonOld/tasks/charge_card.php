<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../api/header/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require_once __DIR__ . '/../../api/models/Admin.php';
require_once __DIR__ . '/../../classes/authorize/CustomerProfile.php';
require_once __DIR__ . '/../../classes/authorize/PaymentProfile.php';

use \classes\authorize\CustomerProfile as Customer;
use \classes\authorize\PaymentProfile as PaymentProfile;

if (isset($_POST['card_number'])) {
    $amount = floatval($_POST['amount']);    
    $card_num 	= $_POST['card_number'];
    $exp_date 	= $_POST['exp_date'];
    $first_name = $_POST['ccfname'];
    $last_name	= $_POST['cclname'];
    $address	= $_POST['ccaddress'];
    $city		= $_POST['cccity'];
    $state		= $_POST['ccstate'];
    $zip		= $_POST['zip'];
    $cvv		= $_POST['cvv'];
    $description = "F" . $admin_id . ":RRFAM-" . $amount;
    
    if (! ($card_num && $exp_date && $first_name && $last_name && $address && $city && $state && $zip && $cvv) ) {
        $error = "All fields are mandatory, please try again.";
        echo $error . "<br />";
    } else {
        require_once __DIR__ . '/../../../includes/authorize.php';
        $charged = false;
        $response = '';
        if ($response_array) {
            if ($response_array[0] == 1) {
                $response .= $response_array[0] . ":";
                $response .= $response_array[3] . ":";
                $response .= $response_array[4] . ":";
                $response .= $response_array[6] . ":";
                $response .= $response_array[9];
                $charged = true;
                echo "Card charged successfully.<br />";
            }
            else {
                $response .= $response_array[3] . "\n";  
                echo "Error charging card: " . $response . "<br />";        
            }
        }
        else {
            echo "No response from authorize.php.<br />";
        }
        exit;
    }
}

$amount = null;
if (isset($_GET['amount'])) {
    $amount = floatval($_GET['amount']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charge Card</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h1 {
            margin: 0 0 1.5rem;
            font-size: 1.5rem;
            color: #333;
        }
        .card-form {
            background: #fff;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
        }
        .form-section {
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #eee;
        }
        .form-section:last-of-type { border-bottom: none; padding-bottom: 0; }
        .form-section-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #666;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #555;
            margin-bottom: 0.35rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.6rem 0.75rem;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #0066cc;
        }
        .row {
            display: flex;
            gap: 1rem;
        }
        .row .form-group { flex: 1; }
        .row .form-group:last-child { flex: 0 0 80px; }
        .row-3 .form-group:nth-child(1) { flex: 1.5; }
        .row-3 .form-group:nth-child(2) { flex: 1; }
        .row-3 .form-group:nth-child(3) { flex: 0 0 90px; }
        button[type="submit"] {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            color: #fff;
            background: #0066cc;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background: #0052a3;
        }
    </style>
</head>
<body>
    <h1>Charge Card</h1>
    <form class="card-form" action="charge_card.php" method="post">
        <div class="form-section">
            <div class="form-section-title">Billing</div>
            <div class="row">
                <div class="form-group">
                    <label for="ccfname">First Name</label>
                    <input type="text" name="ccfname" id="ccfname" placeholder="First" maxlength="50" autocomplete="given-name" required>
                </div>
                <div class="form-group">
                    <label for="cclname">Last Name</label>
                    <input type="text" name="cclname" id="cclname" placeholder="Last" maxlength="50" autocomplete="family-name" required>
                </div>
            </div>
            <div class="form-group">
                <label for="ccaddress">Address</label>
                <input type="text" name="ccaddress" id="ccaddress" placeholder="Street address" maxlength="60" autocomplete="street-address" required>
            </div>
            <div class="row row-3">
                <div class="form-group">
                    <label for="cccity">City</label>
                    <input type="text" name="cccity" id="cccity" placeholder="City" maxlength="40" autocomplete="address-level2" required>
                </div>
                <div class="form-group">
                    <label for="ccstate">State</label>
                    <input type="text" name="ccstate" id="ccstate" placeholder="State" maxlength="2" autocomplete="address-level1" required>
                </div>
                <div class="form-group">
                    <label for="zip">Zip</label>
                    <input type="text" name="zip" id="zip" placeholder="12345" maxlength="12" autocomplete="postal-code" required>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="form-section-title">Card</div>
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" name="card_number" id="card_number" placeholder="4111 1111 1111 1111" maxlength="19" inputmode="numeric" autocomplete="cc-number" required>
            </div>
            <div class="row">
                <div class="form-group">
                    <label for="exp_date">Exp (MMYY)</label>
                    <input type="text" name="exp_date" id="exp_date" placeholder="1225" maxlength="4" inputmode="numeric" autocomplete="cc-exp" required>
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" name="cvv" id="cvv" placeholder="123" maxlength="4" inputmode="numeric" autocomplete="off" required>
                </div>
            </div>
            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="number" name="amount" id="amount" placeholder="0.00" step="0.01" min="0" autocomplete="off" required <?php if (isset($amount)) echo "value='" . htmlspecialchars($amount) . "'"; ?>>
            </div>
            <div class="form-group">
                <label for="admin_id">Enter Family ID</label>
                <input type="text" name="admin_id" id="admin_id" required>
            </div>
        </div>
        <button type="submit">Charge Card</button>
    </form>
</body>
</html>