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
$children = $MASHPIA_DB->prepare("
    SELECT first, paid FROM users u 
    JOIN th_chidon tc using (user_id) 
    JOIN admin_auths aa ON aa.id = u.user_id
    WHERE aa.admin_id = :admin 
    AND tc.year = :year 
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
                border: 1px grey solid;
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
                        <th>Children</th>
                    </tr>
                    <?php
                    foreach ($rows as $row) {
                        // get children info 
                        $res = $children->execute([
                            ':admin' => $row['admin_id'],
                            ':year'  => $year
                        ]);
                        if ($res) {
                            $details = $children->fetchAll();
                        }
                        echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . 
                            $row['admin_address1'] . "<br />" . $row['admin_city'] . ', ' . $row['admin_state'] . ' ' . $row['admin_postal'] . "</td><td>" . 
                            $row['admin_phone_mobile'] . "<br />" . $row['admin_phone_mobile2'] . "</td><td>" . $row['admin_email'] . "</td><td>";
                        foreach ($details as $detail) {
                            echo $detail['first'] . ' - Paid: ' . ($detail['paid'] ? $detail['paid'] : 0) . '<br />';
                        }
                        echo "</td></tr>";
                    }
                    ?>
                </table>
                <?
            }
        }
        ?>
    </body>
</html>