<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';
require_once '../api/header/db.php';
require_once '../class.globalSettings.php';
require_once '../class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], false, true);
$schools = $as->getSchools();

$super = $admin_user['auth'] == 'super';
$current_year = GlobalSettings::getChidonRegYear();

// Get current eligibility list
$eligibility_list = [];
$table_exists = true;

$sql = "SELECT kee.*, u.user_serial, u.first, u.last, u.first_he, u.last_he, u.school_id, s.school_name 
        FROM khk_enrollment_eligibility kee 
        JOIN users u ON kee.user_id = u.user_id 
        LEFT JOIN schools s ON u.school_id = s.school_id
        WHERE kee.year = :year";

if (!$super) {
    $sql .= " AND u.school_id IN (" . implode(',', array_keys($schools)) . ")";
}
$sql .= " ORDER BY u.school_id, u.last, u.first";

$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $current_year]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $eligibility_list[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>KHK Enrollment Eligibility Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <h1>KHK Enrollment Eligibility Management - <?= $current_year ?></h1>
    
    <div id="alert-container" class="mt-3"></div>
    
    <!-- Add User Section -->
    <div class="card my-4">
        <div class="card-body">
            <h5 class="card-title">Add User to Eligibility List</h5>
            <form class="form-inline" onsubmit="addUser(); return false;">
                <div class="form-group mr-3 mb-2">
                    <label for="add-user-input" class="sr-only">User ID or Serial Number</label>
                    <input type="text" class="form-control" id="add-user-input" placeholder="User ID or Serial">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="add-year" class="sr-only">Year</label>
                    <input type="number" class="form-control" id="add-year" value="<?= $current_year ?>">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Add to Eligibility List</button>
            </form>
        </div>
    </div>
    
    <!-- Current Eligibility List -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Current Eligibility List (<?= count($eligibility_list) ?> users)</h5>
            <?php if (empty($eligibility_list)): ?>
                <p class="text-muted">No users currently in the eligibility list for year <?= $current_year ?>.</p>
            <?php else: ?>
                <table class="table table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Serial</th>
                            <th>Name (English)</th>
                            <th>Name (Hebrew)</th>
                            <th>School</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eligibility_list as $user): ?>
                            <tr id="user-row-<?= $user['user_id'] ?>">
                                <td><?= htmlspecialchars($user['user_serial']) ?></td>
                                <td><?= htmlspecialchars($user['first'] . ' ' . $user['last']) ?></td>
                                <td><?= htmlspecialchars($user['first_he'] . ' ' . $user['last_he']) ?></td>
                                <td><?= htmlspecialchars($user['school_name'] ?? 'Unknown') ?></td>
                                <td><?= $user['year'] ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="removeUserFromList(<?= $user['user_id'] ?>, <?= $user['year'] ?>)">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showAlert(message, type = 'success') {
            const alertContainer = $('#alert-container');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHtml = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                  ${message}
                                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </button>
                               </div>`;
            alertContainer.html(alertHtml);

            setTimeout(() => {
                alertContainer.find('.alert').fadeOut();
            }, 5000);
        }
        
        function addUser() {
            const input = $('#add-user-input').val().trim();
            const year = $('#add-year').val();
            
            if (!input) {
                showAlert('Please enter a user ID or serial number.', 'danger');
                return;
            }
            
            $.ajax({
                url: 'api/manageEligibility.php',
                type: 'POST',
                data: {
                    action: 'add',
                    search: input,
                    year: year
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showAlert('User successfully added to eligibility list.');
                        $('#add-user-input').val('');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert(res.error || 'Failed to add user.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Error adding user to eligibility list.', 'danger');
                }
            });
        }
        
        function removeUserFromList(userId, year) {
            if (!confirm('Are you sure you want to remove this user from the eligibility list?')) {
                return;
            }
            
            $.ajax({
                url: 'api/manageEligibility.php',
                type: 'POST',
                data: {
                    action: 'remove',
                    user_id: userId,
                    year: year
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showAlert('User successfully removed from eligibility list.');
                        $('#user-row-' + userId).remove();
                        const newCount = $('tbody tr').length;
                        $('.card-title').first().text(`Current Eligibility List (${newCount} users)`);
                    } else {
                        showAlert(res.error || 'Failed to remove user.', 'danger');
                    }
                },
                error: function() {
                    showAlert('Error removing user from eligibility list.', 'danger');
                }
            });
        }
    </script>
</body>
</html>
