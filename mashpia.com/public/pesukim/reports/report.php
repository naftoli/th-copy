<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.schoolsUsers.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$users = [];
$usersInfo = [];
foreach ($schools as $school_id => $school) {
    $su = new SchoolsUsers($school_id);
    $users[$school_id] = $su->getUsers();
    $usersInfo[$school_id] = $su->getUserInfo();
}

$minutes = [];
foreach ($users as $school => $users) {
    foreach ($users as $user) {
        $pesukim = new Pesukim($user['user_id']);
        $minutes[$school][] = $pesukim->getMinutes();
    }
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
                                    $user_info = $usersInfo[$school_id][$user_id];
                                    $minutes = $minutes[$school_id][$user_id];
                                    ?>
                                    <tr>
                                        <td><? echo $school; ?></td>
                                        <td><? echo $user_info['user_serial']; ?></td>
                                        <td><? echo $user_info['class_grade'] . (empty($user_info['class_sub']) ? '' : '-' . $user_info['class_sub']); ?></td>
                                        <td><? echo $user_info['first']; ?></td>
                                        <td><? echo $user_info['last']; ?></td>
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