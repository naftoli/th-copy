<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$admin_auth = ['school'];
require_once '../header.php';

if ($admin_user['auth'] != 'super') {
    die('Unauthorized');
}

require_once '../../includes/globals.php';
require_once '../api/header/db.php';
require_once '../class.adminSchools.php';

$key = ENCRYPTION_KEY;

// get all test schools
$stmt = $MASHPIA_DB->query("SELECT * FROM schools WHERE test_school = 1");
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

// get all admin info for each school
foreach ($schools as &$school) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM admins a 
        JOIN admin_auths aa ON a.admin_id = aa.admin_id 
        WHERE aa.id = :school_id");
    $stmt->execute(['school_id' => $school['school_id']]);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $school['admins'] = $admins;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Schools</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>School ID</th>
                <th>School Name</th>
                <th>Admins</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schools as $school) { ?>
            <tr>
                <td><?php echo $school['school_id']; ?></td>
                <td><?php echo $school['school_name']; ?></td>
                <td>
                    <?php foreach ($school['admins'] as $admin) { ?>
                        Username: <?php echo $admin['username']; ?>
                        <br />
                        Password: <?php echo decryptPassword($admin['password'], $key); ?>
                        <br />
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>

