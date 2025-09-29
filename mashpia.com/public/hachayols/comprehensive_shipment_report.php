<?php
$admin_auth = ['school'];
require '../header.php';
require_once '../class.globalSettings.php';

require_once 'class.ComprehensiveShipmentReport.php';

$school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 61; // Default to MyShliach
$shipment_number_filter = isset($_GET['shipment_number']) ? intval($_GET['shipment_number']) : 1;

$report = new ComprehensiveShipmentReport($school_id);
$family_shipment_details = $report->getFamilyShipmentDetails($shipment_number_filter);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Comprehensive Shipment Report</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        body { font-family: sans-serif; background-color: #f0f2f5; }
        .page-container { max-width: 1600px; margin: 20px auto; padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        .shipped-yes { color: green; font-weight: bold; }
        .shipped-no { color: red; font-weight: bold; }
        .controls { display: flex; gap: 20px; align-items: center; justify-content: flex-start; background: #f7f7f7; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; }
        .controls form { margin: 0; }
        .controls label { font-weight: bold; }
        .controls select, .controls button { padding: 8px; border-radius: 4px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <div class="page-container">
    <h1>Comprehensive Shipment Report</h1>

    <div class="controls">
        <form method="GET" action="">
            <div>
                <label for="school_id">School:</label>
                <select name="school_id" id="school_id">
                    <option value="61" <?php echo ($school_id == 61) ? 'selected' : ''; ?>>MyShliach</option>
                    <option value="269" <?php echo ($school_id == 269) ? 'selected' : ''; ?>>Anash Kinder</option>
                </select>
            </div>
            <div>
                <label for="shipment_number">Show shipments up to:</label>
                <select name="shipment_number" id="shipment_number">
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($i == $shipment_number_filter) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button type="submit">Generate Report</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Family ID</th>
                <th>Family Name</th>
                <th>Address 1</th>
                <th>Address 2</th>
                <th>City</th>
                <th>State</th>
                <th>Zip</th>
                <th>Country</th>
                <th>Children & Shipment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($family_shipment_details)): ?>
                <?php foreach ($family_shipment_details as $family): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($family['family_id']); ?></td>
                        <td><?php echo htmlspecialchars($family['family_name']); ?></td>
                        <td><?php echo htmlspecialchars($family['address1']); ?></td>
                        <td><?php echo htmlspecialchars($family['address2']); ?></td>
                        <td><?php echo htmlspecialchars($family['city']); ?></td>
                        <td><?php echo htmlspecialchars($family['state']); ?></td>
                        <td><?php echo htmlspecialchars($family['zip']); ?></td>
                        <td><?php echo htmlspecialchars($family['country']); ?></td>
                        <td>
                            <table style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Child Name</th>
                                        <?php for ($i = 1; $i <= $shipment_number_filter; $i++): ?>
                                            <th>Shipment <?php echo $i; ?></th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($family['children'] as $child): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($child['name']); ?></td>
                                            <?php foreach ($child['shipments'] as $is_shipped): ?>
                                                <td>
                                                    <?php if ($is_shipped): ?>
                                                        <span class="shipped-yes">✓</span>
                                                    <?php else: ?>
                                                        <span class="shipped-no">X</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No eligible children found for the selected school.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</body>
</html>
