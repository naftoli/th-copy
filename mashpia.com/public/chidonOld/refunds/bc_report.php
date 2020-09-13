<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = 5780;
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        a.*
    FROM
        admins a
            JOIN
        th_chidon tc ON tc.parent_id = a.admin_id
    WHERE
        tc.year = :year 
    AND tc.date_paid > 0 
    AND a.already_refunded = 0 
    AND tc.school_id = :school
    GROUP BY a.admin_id 
    ORDER BY a.last
");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php include('../../admin_header.php'); ?>
        <h1>Refund Report</h1>
        <p>The following is a report of all parents who have not yet requested a refund.</p>
        <?php
        foreach ($schools as $id => $school)  {
            echo "<h2>" . $school . "</h2>";
            $res = $stmt->execute([
                ':year' => $year, 
                ':school'   => $id
            ]);
            if ($res) {
                $rows = $stmt->fetchAll();
                ?>
                <table>
                    <tr>
                        <th>Parent ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone Numbers</th>
                        <th>Email Address</th>
                    </tr>
                    <?php
                    foreach ($rows as $row) {
                        echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . 
                            $row['admin_address'] . "<br />" . $row['admin_city'] . ', ' . $row['admin_state'] . ' ' . $row['admin_postal'] . "</td><td>" . 
                            $row['admin_phone_mobile'] . "<br />" . $row['admin_phone_mobile2'] . "</td><td>" . $row['admin_email'] . "</td></tr>";
                    }
                    ?>
                </table>
                <?
            }
        }
        ?>
    </body>
</html>