<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../chidon_shipping/class.chidonShipping.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$sql = "SELECT * FROM registration_charges WHERE year = :year";
if ($_GET['schools'] > 0) {
    $sql .= " AND school_id IN (" . $_GET['schools'] . ")";
}
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([
    ':year' => $_GET['year']
]);
$registration_charges = $stmt->fetchAll(PDO::FETCH_ASSOC);

$charges = [];
$summary = [];
$grand_total = 0;
foreach ($registration_charges as $charge) {
    $charges[$charge['type']][] = $charge;
    if (!isset($summary[$charge['type']])) {
        $summary[$charge['type']] = floatval($charge['amount']);
    } else {
        $summary[$charge['type']] += floatval($charge['amount']);
    }
    $grand_total += floatval($charge['amount']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Charges Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Summary Charges Report</h1>
        <p>Generated on <?=date('F j, Y \a\t g:i A')?> for Year <?=$_GET['year']?></p>

        <div class="alert alert-info">
            <strong>Summary:</strong><br>
            <!-- Charge Types: <?//=count($summary)?><br> -->
            Grand Total: $<?=number_format($grand_total, 2)?>
        </div>

        <div class="table-responsive">
            <table id="summaryTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Charge Type</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($summary as $type => $amount) { ?>
                        <tr>
                            <td><?= ChidonShipping::getDescription($type) ? ChidonShipping::getDescription($type) : $type ?></td>
                            <td class="text-end">$<?= number_format($amount, 2) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr class="table-primary">
                        <th>Grand Total</th>
                        <th class="text-end">$<?= number_format($grand_total, 2) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#summaryTable').DataTable({
                responsive: true,
                pageLength: -1, // Show all records
                paging: false, // Disable pagination
                order: [[1, 'desc']], // Sort by amount descending
                language: {
                    search: "Search records:",
                    info: "Showing _TOTAL_ records",
                    infoEmpty: "Showing 0 records",
                    infoFiltered: "(filtered from _MAX_ total records)"
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12"i>>',
                initComplete: function() {
                    $('.dataTables_filter input').attr('placeholder', 'Search...');
                }
            });
        });
    </script>
</body>
</html>