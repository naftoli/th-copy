<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission to be here.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$year = GlobalSettings::getChidonYear();

function getShippingInfo() {
    global $shipping;

    $sql = "select admin_id, first, last, chidon_shipping_paid  
            from admins 
            where chidon_shipping_paid > 0";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $shipping[] = $row;
    }
}

function getExtraPurchases() {
    global $extra_purchases, $year;

    $sqlExtra = "select p.*, a.admin_id, a.first, a.last 
            from extra_purchases p 
            join admins a using (admin_id) 
            where year = " . $year;
    $resExtra = mysql_query($sqlExtra);
    while ($rowExtra = mysql_fetch_assoc($resExtra)) {
        $extra_purchases[] = $rowExtra;
    }
}

function getAddresses() {
    global $addresses;

    $sql = "select * from purchase_addresses";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $addresses[$row['purchase_id']] = $row;
    }
}

$reg = [];
$users = [];
$tracks = [];
$shipping = [];
$extra_purchases = [];
$addresses = [];

getShippingInfo();
getExtraPurchases();
getAddresses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Extra Purchases Report</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            margin: 0;
            padding: 1.5rem 2rem;
            background: #f0f0f0;
            color: #333;
            font-size: 14px;
        }
        h1 {
            margin: 0 0 1.25rem;
            font-size: 1.5rem;
            font-weight: 600;
        }
        .report-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
            max-width: 100%;
        }
        .report-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-section caption {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            background: #f5f5f5;
            border-bottom: 1px solid #e0e0e0;
        }
        .report-section th,
        .report-section td {
            padding: 0.6rem 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .report-section th {
            background: #f8f8f8;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
        }
        .report-section tbody tr:hover {
            background: #fafafa;
        }
        .report-section tbody tr:nth-child(even) {
            background: #fcfcfc;
        }
        .report-section tbody tr:nth-child(even):hover {
            background: #f5f5f5;
        }
        .report-section:first-of-type td:nth-child(4),
        .report-section:last-of-type td:nth-child(5),
        .report-section:last-of-type td:nth-child(9) {
            text-align: right;
        }
        .report-section .empty-row td {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<h1>Extra Purchases Report</h1>

<div class="report-section">
    <table>
        <caption>Shipping Paid</caption>
        <thead>
            <tr>
                <th>Parent ID</th>
                <th>First</th>
                <th>Last</th>
                <th>Amount Paid</th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ($shipping as $row) {
        echo "<tr><td>" . htmlspecialchars($row['admin_id']) . "</td><td>" . htmlspecialchars($row['first'] ?? '') . "</td><td>" . htmlspecialchars($row['last'] ?? '') .
            "</td><td>" . htmlspecialchars($row['chidon_shipping_paid']) . "</td></tr>";
    }
    ?>
        </tbody>
    </table>
</div>

<div class="report-section">
    <table>
        <caption>Extra Purchases</caption>
        <thead>
            <tr>
                <th>Parent ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Item</th>
                <th>Amount</th>
                <th>Sweater Type</th>
                <th>Sweater Size</th>
                <th>Needs Shipping</th>
                <th>Shipping Paid</th>
                <th>Address</th>
            </tr>
        </thead>
        <tbody>
    <?php
    foreach ($extra_purchases as $purchase) {
        echo "<tr><td>" . htmlspecialchars($purchase['admin_id']) . "</td><td>" . htmlspecialchars($purchase['first'] ?? '') . "</td><td>" . htmlspecialchars($purchase['last'] ?? '') .
            "</td><td>" . htmlspecialchars($purchase['item'] ?? '') . "</td><td>" . htmlspecialchars($purchase['amount'] ?? '') . "</td><td>" . htmlspecialchars($purchase['type_of_sweater'] ?? '') .
            "</td><td>" . htmlspecialchars($purchase['size'] ?? '') . "</td><td>";
        echo intval($purchase['shipping_amount']) ? 'Yes' : 'No';
        echo "</td><td>" . htmlspecialchars($purchase['shipping_amount'] ?? '') . "</td><td>";
        if (intval($purchase['shipping_amount']) && isset($addresses[$purchase['purchase_id']])) {
            $ship_info = $addresses[$purchase['purchase_id']];
            echo htmlspecialchars($ship_info['address'] ?? '') . "<br />" . htmlspecialchars($ship_info['city'] ?? '') . ' ' . htmlspecialchars($ship_info['state'] ?? '') . ' ' .
                htmlspecialchars($ship_info['zip'] ?? '') . "<br />" . htmlspecialchars($ship_info['country'] ?? '');
        }
        echo "</td></tr>";
    }
    ?>
        </tbody>
    </table>
</div>
</body>
</html>
