<?php
//error_reporting(E_ALL);
// ini_set('display_errors', 1);

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once( __DIR__ .'/../../header.php');

if ($admin_user['auth'] != 'super') {
   header("Location: /admin.php");
}

require_once( __DIR__ .'/../../api/header/db.php' );
require_once( __DIR__ .'/../../class.adminSchools.php' );
require_once( __DIR__ .'/../../class.globalSettings.php' );
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true, false); // add test schools
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School Registration Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"/>
    
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
            max-width: 1800px;
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
        
        .info-section {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 5px solid #ffc107;
        }
        
        .info-section h3 {
            color: #856404;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .info-section h4 {
            color: #856404;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-section p {
            color: #856404;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .info-section ol, .info-section ul {
            color: #856404;
            margin-left: 20px;
            margin-bottom: 15px;
        }
        
        .info-section li {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
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
            font-size: 0.9rem;
        }
        
        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color), #2980b9);
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }
        
        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.3);
        }
        
        .status-paid {
            background: linear-gradient(135deg, var(--success-color), #229954);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-inactive {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
        
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 15px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .form-control, .form-select {
                font-size: 0.8rem;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="bi bi-gear"></i> School Registration Settings</h1>
        </div>

        <div class="info-section">
            <h3><i class="bi bi-info-circle"></i> School Registration Settings</h3>
            
            <h4><i class="bi bi-question-circle"></i> Settings Explained:</h4>
            
            <p>
                <strong>Year:</strong> Current Chayolei Registration year for this base. (Australian/South African schools may be different)
            </p>

            <p><strong>Registration Type:</strong></p>
            <ol>
                <li><strong>In Tuition:</strong> The base charges parents for registration in Tuition.</li>
                <li><strong>Guaranteed:</strong> 
                    Parents are given an additional $<?=GlobalSettings::getGuaranteedDiscount()?> discount as base guarantees all parents will register. 
                    If some parents do not register the base is charged for the discount given to all soldiers who registered.</li>
                <li><strong>By Parent:</strong> Parents pay for registration as they wish.</li>
            </ol>

            <p><strong>School Charge Date:</strong> This is for Tuition / Guaranteed schools. It's the date that HQ should charge them for any unregistered children.</p>

            <p><strong>Chayolei Fee:</strong> The fee the base will pay to register.</p>

            <p><strong>Balance:</strong> The balance the base owes to Tzivos Hashem.</p>

            <p><strong>Soldier Chayolei Fee:</strong> The registration fee for soldiers in this base.</p>
            <ul>
                <li>Please note that the early bird discount ($<?=GlobalSettings::getEarlyBird()?>) is applied to this amount.</li>
                <li>For example, $55 soldier fee - $5 early bird is $50 for registration.</li>
                <li><em>Set to / leave as <strong>Blank</strong> for default rates.</em></li>
            </ul>

            <p><strong>Early Bird:</strong> The date on which the early bird ends for the base.<br/>
                For "guaranteed" bases this is also the deadline to have all children register.
            </p>

            <p><strong>Status:</strong> The current status of the base</p>
            <ol>
                <li><strong>Deactivate (button):</strong> Revoke base commanders access to their base until they pay for registration.</li>
                <li><strong>Inactive:</strong> Base commanders are locked out of this base until registration is paid.</li>
                <li><strong>Paid:</strong> the base has gone through registration and paid.</li>
            </ol>
        </div>
        
        <div id="report" style="display: none;">
            <div class="table-container">
                <div class="table-responsive">
                    <table id="registration-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Base #</th>
                                <th>Base Name</th>
                                <th>Registration Type</th>
                                <th>School Charge Date</th>
                                <th>Chayolei Fee</th>
                                <th>Chidon Fee</th>
                                <th>Balance</th>
                                <th>Soldier Chayolei Fee</th>
                                <th>Early Bird</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach( $schools as $school_id => $school_name ) {
                            $base = \School::find([$school_id]);
                            $year = GlobalSettings::getRegistrationYear( $base->school_id ); ?>
                            <tr class='base' data-school_id='<?= $base->school_id; ?>' data-year='<?= $year ?>'>
                                <td class="text-center"><?= $year ?></td>
                                <td class="text-center"><strong><?= $base->school_number ?></strong></td>
                                <td><strong><?= $base->name ?></strong></td>
                                <td>
                                    <select name="reg_type" class="form-select form-select-sm">
                                        <option value="0" <?= !$base->reg_type ? 'selected' : ''; ?> disabled>N/A</option>
                                        <option value="1" <?= $base->reg_type == '1' ? 'selected' : ''; ?>>In Tuition</option>
                                        <option value="2" <?= $base->reg_type == '2' ? 'selected' : ''; ?>>Guaranteed</option>
                                        <option value="3" <?= $base->reg_type == '3' ? 'selected' : ''; ?>>By Parent</option>
                                    </select>
                                </td>
                                <td>
                                    <?php
                                    if (!$base->school_charge_date) {
                                        echo "<span class='text-muted'>n/a</span>";
                                    } else { ?>
                                    <input type="date" name="school_charge_date" class="form-control form-control-sm"
                                           value="<?= date('Y-m-d', strtotime($base->school_charge_date)) ?>" />
                                    <?php } ?>
                                </td>
                                <td>
                                    <input type='number' name='chayolei_fee' class="form-control form-control-sm"
                                        value='<?= $base->chayolei_fee ?>' step="0.01" />
                                </td>
                                <td>
                                    <input type='number' name='chidon_fee' class="form-control form-control-sm"
                                        value='<?= $base->chidon_fee ?>' step="0.01" />
                                </td>
                                <td>
                                    <input type='number' name='balance' class="form-control form-control-sm"
                                        value='<?= $base->balance ?>' step="0.01" />
                                </td>
                                <td>
                                    <input type='number' name='child_fee' class="form-control form-control-sm"
                                        value='<?= $base->child_fee ?>' step="0.01" />
                                </td>
                                <td>
                                    <input type='date' name='early_bird' class="form-control form-control-sm"
                                        value='<?= $base->earlyBird()->format('Y-m-d') ?>' />
                                </td>
                                <td>
                                    <textarea rows="3" cols="15" name="registration_notes" id="notes" class="form-control form-control-sm"><?= $base->registration_notes ?></textarea>
                                </td>
                                <td class="text-center">
                                <?php
                                    if ( $base->registration( $year ) ) {
                                        echo '<span class="status-paid">Paid</span>';
                                    } else if ( $base->school_era ) {
                                        echo '<span class="status-inactive">Inactive</span>';
                                    } else { ?>
                                        <button class='btn btn-danger btn-sm deactivate'>Deactivate</button>
                                <?php } ?>
                                </td>
                                <td class="text-center">
                                    <button class='btn btn-primary btn-sm save' disabled>Save Changes</button>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.polyfill.io/v2/polyfill.min.js"></script>
    <script src="js/registration_info.js"></script>
    
    <script>
        $(function() {
            // Initialize DataTable
            $('#registration-table').DataTable({
                responsive: false,
                paging: false,
                ordering: true,
                info: false,
                order: [[2, 'asc']],
                language: {
                    search: "Search schools:",
                    lengthMenu: "Show _MENU_ schools per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ schools",
                    infoEmpty: "No schools to show",
                    infoFiltered: "(filtered from _MAX_ total schools)"
                },
                columnDefs: [
                    { targets: [0,1], className: 'text-center' },
                    { targets: [4,9], className: 'text-center' },
                    { targets: [5,6,7,8], className: 'text-end' },
                    { targets: [11,12], className: 'text-center' }
                ]
            });
        });
    </script>
</body>
</html>