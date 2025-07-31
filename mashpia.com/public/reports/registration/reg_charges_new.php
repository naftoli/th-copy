<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = array();
require_once(__DIR__ . '/../../header.php');

require_once(__DIR__ . '/../../class.globalSettings.php');
$year = GlobalSettings::getRegistrationYear();

require_once(__DIR__ . '/../../chidon_shipping/class.chidonShipping.php');

// get the totals
$totals = [];
$total_query = mysql_query(
    "SELECT type, SUM( amount ) AS total 
    FROM registration_charges 
    WHERE year = $year AND refunded = 0 
    GROUP BY type ORDER BY type;"
);
$grand_total = 0;
while ($row = mysql_fetch_assoc($total_query)) {
    $grand_total += intval($row['total']);
    $totals[ChidonShipping::getDescription($row['type'])] = intval($row['total']);
}
ksort($totals);

// get the details
$details = [];
$detail_query = mysql_query(
    "SELECT s.school_name, s.school_number, u.user_serial, u.first, u.last, rc.registration_charge_id, rc.type, rc.date, 
      rc.year, rc.amount, rc.refunded "
    . "FROM registration_charges rc LEFT JOIN schools s USING ( school_id ) "
    . "LEFT JOIN users u USING ( user_id ) LEFT JOIN transactions t USING ( trans_id ) "
    . "WHERE year = $year ORDER BY rc.date DESC, school_name, u.first, u.last, rc.amount;"
);
while ($row = mysql_fetch_assoc($detail_query)) $details[] = $row;
//echo "<pre>"; print_r($details); echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $year ?> Registration Charges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 300;
            font-size: 2.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stats-card h2 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 20px;
            font-weight: 300;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.2s ease;
        }
        
        .btn-refund {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .btn-refund:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(238, 90, 36, 0.4);
            color: white;
        }
        
        .btn-refund:disabled {
            background: #6c757d;
            transform: none;
            box-shadow: none;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
        }
        
        .badge-refunded {
            background-color: #dc3545;
        }
        
        .badge-active {
            background-color: #28a745;
        }
        
        .amount-cell {
            font-weight: 600;
            color: #28a745;
        }
        
        .school-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .user-name {
            font-weight: 500;
            color: #495057;
        }
        
                 .registration-type {
             font-weight: 500;
             color: #667eea;
         }
         

    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="main-container">
            <div class="page-header">
                <h1><i class="fas fa-chart-line me-3"></i><?= $year ?> Soldier Registration Charges</h1>
            </div>
            
            <!-- Totals Section -->
            <div class="stats-card">
                <h2><i class="fas fa-calculator me-2"></i>Financial Summary</h2>
                <div class="row">
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-borderless text-white">
                                <thead>
                                    <tr>
                                        <th>Registration Type</th>
                                        <th class="text-end">Total Received</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($totals as $type => $total) { ?>
                                        <tr>
                                            <td><?= $type ?></td>
                                            <td class="text-end">$<?= number_format($total) ?></td>
                                        </tr>
                                    <?php } ?>
                                    <tr class="border-top">
                                        <th>Grand Total</th>
                                        <th class="text-end">$<?= number_format($grand_total) ?></th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                            <h3 class="mb-0">$<?= number_format($grand_total) ?></h3>
                            <p class="mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
            
                         <!-- Details Section -->
             <div class="card">
                 <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                     <h3 class="mb-0"><i class="fas fa-list me-2"></i>Registration Details</h3>
                     <button class="btn btn-outline-light btn-sm clear-filters" title="Clear all filters">
                         <i class="fas fa-times me-1"></i>Clear Filters
                     </button>
                 </div>
                 

                <div class="card-body p-0">
                    <div class="table-responsive">
                                                 <table id="detailsTable" class="table table-striped table-hover mb-0">
                             <thead>
                                 <tr>
                                     <th>School Info</th>
                                     <th>Student</th>
                                     <th>Registration Type</th>
                                     <th>Registration Date</th>
                                     <th>Amount</th>
                                     <th>Status</th>
                                     <th>Actions</th>
                                 </tr>
                             </thead>
                            <tbody>
                                <?php foreach ($details as $detail) { ?>
                                    <tr id="<?= $detail['registration_charge_id'] ?>">
                                        <td>
                                            <div class="school-info">
                                                <strong><?= $detail['school_number'] ? "#" . $detail['school_number'] : "" ?></strong><br>
                                                <small><?= $detail['school_name'] ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-name"><?= $detail['first'] . " " . $detail['last'] ?></div>
                                            <small class="text-muted"><?= $detail['user_serial'] ? "#" . $detail['user_serial'] : "" ?></small>
                                        </td>
                                        <td>
                                            <div class="registration-type"><?= ChidonShipping::getDescription($detail['type']) ?></div>
                                            <small class="text-muted">Code: <?= $detail['type'] ?></small>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            <?= (new DateTime($detail['date']))->format('m/d/Y g:i:sa'); ?>
                                        </td>
                                        <td class="amount-cell">$<?= number_format($detail['amount'], 2) ?></td>
                                        <td>
                                            <?php if (intval($detail['refunded'])) { ?>
                                                <span class="badge badge-refunded">
                                                    <i class="fas fa-undo me-1"></i>Refunded
                                                </span>
                                            <?php } else { ?>
                                                <span class="badge badge-active">
                                                    <i class="fas fa-check me-1"></i>Active
                                                </span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-refund btn-sm"
                                                    <?php if (intval($detail['refunded'])) echo " disabled"; ?>
                                                    data-amount="<?= $detail['amount'] ?>"
                                                    data-type="<?= $detail['type'] ?>"
                                                    data-serial="<?= $detail['user_serial'] ?>">
                                                <i class="fas fa-undo me-1"></i>Refund
                                            </button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">
                        <i class="fas fa-undo me-2"></i>Process Refund
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="refundForm">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Refund *</label>
                            <input type="text" class="form-control" id="reason" name="reason" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="duplicate" name="duplicate" value="1">
                                <label class="form-check-label" for="duplicate">
                                    Duplicate Charge
                                </label>
                            </div>
                        </div>
                        <input type="hidden" name="refund_id" id="refund_id">
                        <input type="hidden" name="amount" id="amount">
                        <input type="hidden" name="type" id="type">
                        <input type="hidden" name="serial" id="serial">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="refund">
                        <i class="fas fa-undo me-1"></i>Process Refund
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
         <script>
         $(document).ready(function () {
             // Initialize DataTable with simple configuration
             var table = $('#detailsTable').DataTable({
                 pageLength: 100,
                 lengthMenu: [[25, 50, 100, 500, 1000, 5000, 10000], [25, 50, 100, 500, 1000, 5000, 10000]],
                 order: [[3, 'desc']], // Sort by date descending by default
                 responsive: true,
                 language: {
                     search: "Search records:",
                     lengthMenu: "Show _MENU_ records per page",
                     info: "Showing _START_ to _END_ of _TOTAL_ records",
                     paginate: {
                         first: "First",
                         last: "Last",
                         next: "Next",
                         previous: "Previous"
                     }
                 },
                 columnDefs: [
                     { orderable: false, targets: [6] } // Disable sorting for Actions column
                 ]
             });

            // Refund button click handler
            $('.btn-refund').click(function () {
                const row = $(this).closest('tr');
                const refundId = row.attr('id');
                const amount = $(this).data('amount');
                const type = $(this).data('type');
                const serial = $(this).data('serial');
                
                // Reset form
                $('#reason').val('');
                $('#duplicate').prop('checked', false);
                
                // Set hidden values
                $('#refund_id').val(refundId);
                $('#amount').val(amount);
                $('#type').val(type);
                $('#serial').val(serial);
                
                // Show modal
                $('#refundModal').modal('show');
            });

            // Process refund
            $("#refund").click(function() {
                const id = $('#refund_id').val();
                const amount = $('#amount').val();
                const reason = $('#reason').val();
                const type = $('#type').val();
                const serial = $('#serial').val();
                const duplicate = $('#duplicate').is(':checked') ? 1 : 0;
                
                if (!reason) {
                    alert('Please enter a reason for the refund!');
                    return false;
                }
                
                if (!(id && amount && type && serial)) {
                    alert('Cannot submit refund, missing data!');
                    return false;
                }
                
                // Disable button during processing
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');
                
                $.ajax({
                    url: 'refund.php',
                    type: 'POST',
                    data: {
                        id,
                        amount,
                        reason,
                        type,
                        serial,
                        duplicate
                    },
                    success: function (data) {
                        if (data.success) {
                            // Update UI
                            const row = $('#' + id);
                            row.find('td:nth-child(6)').html('<span class="badge badge-refunded"><i class="fas fa-undo me-1"></i>Refunded</span>');
                            row.find('.btn-refund').prop('disabled', true);
                            
                            // Close modal
                            $('#refundModal').modal('hide');
                            
                            // Show success message
                            alert('Refund processed successfully!');
                            
                            // Reload page to update totals
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            alert('Error: ' + (data.error || data));
                            console.log(data.error_msg);
                        }
                    },
                    error: function() {
                        alert('An error occurred while processing the refund.');
                    },
                    complete: function() {
                        // Re-enable button
                        $("#refund").prop('disabled', false).html('<i class="fas fa-undo me-1"></i>Process Refund');
                    }
                });
            });
        });
    </script>
</body>
</html>