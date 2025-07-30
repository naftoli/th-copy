<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once ( __DIR__ . '/../../header.php' );

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$cur_year = GlobalSettings::getRegistrationYear();
$year = $cur_year;
if (isset($_GET['year'])) $year = $_GET['year'];

require_once __DIR__ . '/../../class.adminSchools.php';
$s = new AdminSchools($admin_user['admin_id'] ,$admin_user['auth'], true, true, false);
$all_schools = $s->getSchools();

$types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];

$main_query = "
    SELECT 
        school_id,
        school_name,
        school_number,
        s.balance, 
        reg_type,
        chayolei_fee,
        chidon_fee,
        school_registration_id,
        date_paid,
        amount_paid, 
        discount,
        total_chayolei,
        total_chidon,
        total_balance_paid,
        total_registered,
        total_chayolei_eligible, 
        total_chidon_eligible
    FROM
        school_registrations sr
            JOIN
        schools s USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_chayolei
        FROM
            school_registration_details
        WHERE
            year = $year 
            AND (type = 'chayolei' or type = '' or type is null)
        GROUP BY school_id) chayolei USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_chidon
        FROM
            school_registration_details
        WHERE
            type = 'chidon' AND year = $year
        GROUP BY school_id) chidon USING (school_id)
             LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_balance_paid 
        FROM
            school_registration_details
        WHERE
            type = 'past_due' AND year = $year
        GROUP BY school_id) balance USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS discount 
        FROM
            school_registration_details
        WHERE
            type = 'discount' AND year = $year
        GROUP BY school_id) discount USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_registered
        FROM
            users  
        WHERE
            user_registered > 0 
        GROUP BY school_id) reg USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_chayolei_eligible
        FROM
            users
        WHERE
            chayolei_eligible = 1
        GROUP BY school_id) chayolei_el USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_chidon_eligible
        FROM
            users
        WHERE
            chidon_eligible = 1
        GROUP BY school_id) chidon_el USING (school_id) 
    WHERE
        sr.year = $year 
";
$main_query = mysql_query( $main_query );
$data = [];
while( $row = mysql_fetch_assoc( $main_query ) ) $data[$row['school_name']] = $row;
ksort($data);
//echo "<pre>"; print_r($data); echo "</pre>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Registration Charges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"/>
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin: 20px auto;
            padding: 30px;
            max-width: 1400px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 300;
            font-size: 2.5rem;
        }
        
        .control-panel {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .control-panel h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            border-radius: 6px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }
        
        .alert-custom {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid #ffc107;
        }
        
        .alert-custom h5 {
            color: #856404;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .alert-custom p {
            color: #856404;
            margin-bottom: 0;
            line-height: 1.6;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px 10px;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .balance-negative {
            background-color: rgba(231, 76, 60, 0.1) !important;
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .balance-positive {
            background-color: rgba(39, 174, 96, 0.1) !important;
            color: var(--success-color);
            font-weight: 600;
        }
        
        .totals-row {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            color: white;
            font-weight: 600;
        }
        
        .totals-row th {
            border: none;
            padding: 15px 10px;
        }
        
        .dataTables_wrapper {
            padding: 20px;
        }
        
        .dataTables_filter input {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
        }
        
        .dataTables_length select {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 6px 10px;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-clipboard-data"></i> <?=$year?> Base Registration Status</h1>
        </div>
        
        <!-- Year Selection -->
        <div class="control-panel">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label for="year" class="form-label"><i class="bi bi-calendar"></i> Choose Year:</label>
                    <select name="year" id="year" class="form-select">
                        <?php
                        for ($y = $cur_year; $y >= 5777; $y--) {
                            echo "<option value='$y'";
                            if ($y == $year)
                                echo " selected";
                            echo ">$y</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Add Payment Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-plus-circle"></i> Add Payment
            </div>
            <div class="card-body">
                <form id="add_payment_form">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="school_payment" class="form-label">School:</label>
                            <select name="school_payment" id="school_payment" class="form-select">
                                <option value="0">Choose School</option>
                                <?php
                                foreach ($all_schools as $id => $school) {
                                    echo "<option value='$id'>$school</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="payment_type" class="form-label">Payment Type:</label>
                            <select name="payment_type" id="payment_type" class="form-select">
                                <option value="0">Choose Payment Type</option>
                                <option value="chayolei">Chayolei</option>
                                <option value="chidon">Chidon</option>
                                <option value="tanya">Tanya</option>
                                <option value="rewards">Rewards Program</option>
                                <option value="past_due">Past Dues</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="payment_method" class="form-label">Payment Method:</label>
                            <select name="payment_method" id="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="wire">Wire Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="payment_amount" class="form-label">Amount:</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" name="payment_amount" id="payment_amount" class="form-control" placeholder="0.00" />
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" id="add_payment" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Add Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Important Notes -->
        <div class="alert-custom">
            <h5><i class="bi bi-exclamation-triangle"></i> Important Information</h5>
            <p>
                <strong>Financial Report Note:</strong> This report is a <strong>financial</strong> report, and as such, it shows how many children are in each school based
                on the <strong>charges</strong>. Therefore, when there are multiple charges for one child (whether they paid twice, or three times, etc),
                it is counted as 2 or 3 kids. As a result, this number may be completely different than the number of registered children
                that is being shown on the home page of the base commander's site, or any other reports. (This can also include
                situations where the child paid, and then "unenrolled" but was never removed from payment database).
            </p>
        </div>
        
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>Important:</strong> In order to change an amount to 0 you MUST ENTER 0. If you leave the field BLANK it will NOT change the amount!
        </div>
        
        <!-- Data Table -->
        <div class="table-container">
            <table id="table" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Base Type</th>
                        <th>Base Number</th>
                        <th>Base Name</th>
                        <th>Date Registered</th>
                        <th>Chayolei Fee</th>
                        <th>Chayolei Paid</th>
                        <th>Chidon Fee</th>
                        <th>Chidon Paid</th>
                        <th>Prior Balance</th>
                        <th>Prior Balance Paid</th>
                        <th>Total Owing</th>
                        <th>Discount</th>
                        <th>Total Paid</th>
                        <th>Current Balance</th>
                        <th>Eligible Chayolei Soldiers</th>
                        <th>Chayolei Soldiers Registered</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totals['chayolei_fee'] = 0;
                $totals['chayolei_paid'] = 0;
                $totals['chidon_fee'] = 0;
                $totals['chidon_paid'] = 0;
                $totals['prior_balance'] = 0;
                $totals['prior_balance_paid'] = 0;
                $totals['owing'] = 0;
                $totals['discount'] = 0;
                $totals['total_paid'] = 0;
                $totals['current_balance'] = 0;
                foreach( $data as $base ) {
                    if ( !$base['total_registered'] ) $base['total_registered'] = 0;
                    $total_owing = floatval($base['chayolei_fee']) + floatval($base['chidon_fee']) + floatval($base['balance']);
                    $total_paid = floatval($base['total_chayolei']) + floatval($base['total_chidon']) +
                        floatval($base['total_balance_paid']) + ($base['discount'] ? floatval($base['discount']) : 0);
                    $total_paid = $total_paid == 0 ? floatval($base['amount_paid']) : $total_paid;
                    $balance = ($total_owing + $base['discount']) - $total_paid;
                    
                    $balance_class = '';
                    if ($balance > 0) {
                        $balance_class = 'balance-negative';
                    } elseif ($balance < 0) {
                        $balance_class = 'balance-positive';
                    }
                    ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= $types[$base['reg_type']] ?></span></td>
                        <td><strong><?= $base['school_number'] ?></strong></td>
                        <td><strong><?= $base['school_name'] ?></strong></td>
                        <td>
                            <?php if ($base['date_paid']): ?>
                                <i class="bi bi-calendar-check text-success"></i>
                                <?= (new DateTime($base['date_paid']))->format('m/d/Y g:i:s') ?>
                            <?php else: ?>
                                <span class="text-muted"><i class="bi bi-calendar-x"></i> Not Registered</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">$<?= number_format($base['chayolei_fee'], 2) ?></td>
                        <td class="text-end">$<?= number_format($base['total_chayolei'], 2) ?></td>
                        <td class="text-end">$<?= number_format($base['chidon_fee'], 2) ?></td>
                        <td class="text-end">$<?= number_format($base['total_chidon'], 2) ?></td>
                        <td class="text-end">$<?= number_format($base['balance'], 2) ?></td>
                        <td class="text-end">$<?= number_format($base['total_balance_paid'], 2) ?></td>
                        <td class="text-end"><strong>$<?= number_format($total_owing, 2) ?></strong></td>
                        <td class="text-end">$<?= number_format($base['discount'] ? $base['discount'] : 0, 2) ?></td>
                        <td class="text-end"><strong>$<?= number_format($total_paid, 2) ?></strong></td>
                        <td class="text-end <?= $balance_class ?>">
                            <strong>$<?= number_format($balance, 2) ?></strong>
                        </td>
                        <td class="text-center"><?= $base['total_chayolei_eligible'] ?></td>
                        <td class="text-center"><?= $base['total_registered'] ?></td>
                    </tr>
                    <?php
                    $totals['chayolei_fee'] += $base['chayolei_fee'];
                    $totals['chayolei_paid'] += $base['total_chayolei'];
                    $totals['chidon_fee'] += $base['chidon_fee'];
                    $totals['chidon_paid'] += $base['total_chidon'];
                    $totals['prior_balance'] += $base['balance'];
                    $totals['prior_balance_paid'] += $base['total_balance_paid'];
                    $totals['owing'] += $total_owing;
                    $totals['discount'] += $base['discount'];
                    $totals['total_paid'] += $total_paid;
                    $totals['current_balance'] += $balance;
                }
                ?>
                </tbody>
                <tfoot>
                    <tr class="totals-row">
                        <th colspan="3"></th>
                        <th><i class="bi bi-calculator"></i> Totals:</th>
                        <th class="text-end">$<?= number_format($totals['chayolei_fee'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['chayolei_paid'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['chidon_fee'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['chidon_paid'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['prior_balance'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['prior_balance_paid'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['owing'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['discount'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['total_paid'], 2) ?></th>
                        <th class="text-end">$<?= number_format($totals['current_balance'], 2) ?></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <script>
        $(function() {
            // Initialize DataTable
            $('#table').DataTable({
                responsive: true,
                paging: false,
                ordering: true,
                info: false,
                order: [[2, 'asc']],
                language: {
                    search: "Search bases:",
                    lengthMenu: "Show _MENU_ bases per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ bases",
                    infoEmpty: "No bases to show",
                    infoFiltered: "(filtered from _MAX_ total bases)"
                },
                columnDefs: [
                    { targets: [4,5,6,7,8,9,10,11,12,13], className: 'text-end' },
                    { targets: [14,15], className: 'text-center' }
                ]
            });

            // Add payment form submission
            $("#add_payment_form").on('submit', function(e) {
                e.preventDefault();
                const school = $("#school_payment").val();
                const method = $("#payment_method").val();
                const type = $("#payment_type").val();
                const amount = parseFloat($("#payment_amount").val());
                
                if (school == '0') {
                    showAlert('You must choose a school', 'warning');
                    return;
                }
                if (!amount) {
                    showAlert('You must enter an amount!', 'warning');
                    return;
                }
                
                // Show loading state
                const btn = $("#add_payment");
                const originalText = btn.html();
                btn.html('<i class="bi bi-hourglass-split"></i> Processing...');
                btn.prop('disabled', true);
                
                $.post('addPayment.php', { 
                    school: school, 
                    method: method, 
                    type: type, 
                    amount: amount 
                }, function(result) {
                    try {
                        const res = JSON.parse(result);
                        if (res.success) {
                            showAlert('Payment added successfully!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showAlert(res.error || 'An error occurred', 'danger');
                        }
                    } catch (e) {
                        showAlert('An error occurred while processing the response', 'danger');
                    }
                }).fail(function() {
                    showAlert('Network error occurred', 'danger');
                }).always(function() {
                    btn.html(originalText);
                    btn.prop('disabled', false);
                });
            });

            // Year change handler
            $("#year").change(function() {
                let y = $(this).val();
                let url = location.href.split('?')[0];
                location.href = url + '?year=' + y;
            });

            // Helper function to show alerts
            function showAlert(message, type) {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'x-circle'}"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('.main-container').prepend(alertHtml);
                
                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        });
    </script>
</body>
</html>
