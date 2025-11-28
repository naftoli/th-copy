<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukimTotals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$pesukimTotals = new PesukimTotals();
$minutes = $pesukimTotals->getMinutesByUser(array_keys($schools));

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
                            foreach ($users as $school_id => $users) { 
                                foreach ($users as $user) { 
                                    $user_id = $user['user_id'];
                                    $minutes = $minutes[$user['user_id']];
                                    ?>
                                    <tr>
                                        <td><? echo $schools[$school_id]; ?></td>
                                        <td><? echo $user['user_serial']; ?></td>
                                        <td><? echo $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
                                        <td><? echo $user['first']; ?></td>
                                        <td><? echo $user['last']; ?></td>
                                        <td><? echo $minutes; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</html>