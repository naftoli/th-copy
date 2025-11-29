<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukimTotals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true);
$schools = $as->getSchools();

$pesukimTotals = new PesukimTotals();
$schoolsForReport = $admin_user['auth'] == 'super' ? [] : array_keys($schools);
$minutes = $pesukimTotals->getMinutesByUser($schoolsForReport);

$users = [];
$usersInfo = [];
foreach ($schools as $school_id => $school) {
    $su = new SchoolsUsers($school_id);
    $users[$school_id] = $su->getUsers();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pesukim Report</title>
        <!-- get bootstrap from cdn -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    </head>
    <body>
        <h1>Pesukim Report</h1>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>User Serial</th>
                                <th>Class</th>
                                <th>First</th>
                                <th>Last</th>
                                <th>Minutes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totals = [];
                            foreach ($users as $school_id => $users) { 
                                $total = 0;
                                foreach ($users as $user) { 
                                    $total += $minutes[$user['user_id']] ?? 0;
                                    ?>
                                    <tr>
                                        <td><? echo $schools[$school_id]; ?></td>
                                        <td><? echo $user['user_serial']; ?></td>
                                        <td><? echo $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
                                        <td><? echo $user['first']; ?></td>
                                        <td><? echo $user['last']; ?></td>
                                        <td><? echo $minutes[$user['user_id']] ?? 0; ?></td>
                                    </tr>
                                    <?php
                                }
                                $totals[$school_id] = $total;
                            }
                            ?>
                        </tbody>
                    </table>
                    <br />
                    <h2>Totals</h2>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $grandTotal = 0;
                        foreach ($totals as $school_id => $total) {
                            echo "<tr><td>$schools[$school_id]</td><td>$total</td></tr>";
                            $grandTotal += $total;
                        }
                        echo "<tr><td><b>Grand Total:</b></td><td><b>$grandTotal</b></td></tr>";
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>