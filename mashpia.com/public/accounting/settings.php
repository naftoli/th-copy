<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

require_once '../api/header/db.php';
require_once '../class.adminSchools.php';
require_once '../class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true, false);
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registration Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        .table {
            width: 100%;
        }
        .table th,
        .table td {
            white-space: nowrap;
            min-width: 120px;
        }
        .table th {
            position: sticky;
            top: 0;
            background-color: #212529;
            z-index: 1;
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
        .status-paid {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-inactive {
            background: #ffc107;
            color: #212529;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <h1>School Registration Settings</h1>
        <p>Manage school registration settings and fees.</p>

        <div class="alert-custom">
            <h5><i class="bi bi-info-circle"></i> Settings Explained</h5>
            <p>
                <strong>Year:</strong> Current Chayolei Registration year for this base.<br>
                <strong>Registration Type:</strong> In Tuition (base charges parents), Guaranteed (base guarantees registration), By Parent (parents pay directly)<br>
                <strong>School Charge Date:</strong> Date HQ should charge for unregistered children (Tuition/Guaranteed schools)<br>
                <strong>Chayolei Fee:</strong> Fee the base will pay to register<br>
                <strong>Balance:</strong> Balance the base owes to Tzivos Hashem<br>
                <strong>Soldier Chayolei Fee:</strong> Registration fee for soldiers (early bird discount applied)<br>
                <strong>Early Bird:</strong> Date early bird discount ends (also deadline for guaranteed bases)<br>
                <strong>Status:</strong> Paid (registration complete), Inactive (locked out), or Deactivate button
            </p>
        </div>

        <div id="report" style="display: none;">
            <div class="table-responsive">
                <table id="registration-table" class="table table-striped table-bordered">
                    <thead class="table-dark">
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
                                    value='<?= $base->chayolei_fee ?>' step="1" />
                            </td>
                            <td>
                                <input type='number' name='chidon_fee' class="form-control form-control-sm"
                                    value='<?= $base->chidon_fee ?>' step="1" />
                            </td>
                            <td>
                                <input type='number' name='balance' class="form-control form-control-sm"
                                    value='<?= $base->balance ?>' step="1" />
                            </td>
                            <td>
                                <input type='number' name='child_fee' class="form-control form-control-sm"
                                    value='<?= $base->child_fee ?>' step="1" />
                            </td>
                            <td>
                                <input type='date' name='early_bird' class="form-control form-control-sm"
                                    value='<?= $base->earlyBird()->format('Y-m-d') ?>' />
                            </td>
                            <td>
                                <textarea rows="3" cols="15" name="registration_notes" class="form-control form-control-sm"><?= $base->registration_notes ?></textarea>
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

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(function() {
            // Password protection
            let pass = '';
            while (pass !== 'zelda@5780') {
                pass = prompt("Please enter the password.", pass);
                if (pass === null) {
                    window.location.href = 'index.php';
                    return;
                }
            }
            $("#report").show();

            // Initialize DataTable
            $('#registration-table').DataTable({
                responsive: true,
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

            // Setup event listeners
            $('.base input, .base select, .base textarea').change(onChange);
            $('.base button.deactivate').click(deactivate);
            $('.base button.save').click(save);

            // Show save button when changes are made
            function onChange(event) {
                var tr = $(event.target).closest('tr');
                tr.find('.save').prop('disabled', false);
            }

            // Deactivate the base
            function deactivate(event) {
                var tr = $(event.target).closest('tr');
                var school_id = tr.data('school_id');
                var year = tr.data('year');
                
                updateBase(school_id, { school_era: year })
                    .then(function(response) {
                        if (response.success) {
                            event.target.parentElement.innerHTML = '<span class="status-inactive">Inactive</span>';
                        }
                    })
                    .catch(error => console.log(error));
            }

            // Save changes
            function save(event) {
                var tr = $(event.target).closest('tr');
                var school_id = tr.data('school_id');
                const updates = tr.find('select, input, textarea').toArray()
                    .reduce(function(obj, input) {
                        return Object.assign({}, obj, { [input.name]: input.value });
                    }, {});

                if (updates.child_fee == '') {
                    updates.child_fee = null;
                }

                updateBase(school_id, updates)
                    .then(function(response) {
                        if (response.success) {
                            event.target.disabled = true;
                            alert('Base Updated');
                        } else {
                            alert(response.message || 'Error updating base');
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        alert('Error updating base');
                    });
            }

            function updateBase(school_id, updates) {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: '/api/core/bases?id=' + school_id,
                        type: 'POST',
                        data: JSON.stringify(updates),
                        error: reject,
                        dataType: "json",
                        success: resolve,
                        contentType: "application/json; charset=utf-8",
                    });
                });
            }
        });
    </script>
</body>
</html>
