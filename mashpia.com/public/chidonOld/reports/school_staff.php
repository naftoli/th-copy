<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    SELECT tc.*, s.school_name FROM th_chidon_chaps tc 
    JOIN schools s using (school_id) 
    WHERE year = :year
");
$stmt->execute([':year' => $year]);
$rows = $stmt->fetchAll();

$info = [];
foreach ( $rows as $row ) {
    $info[$row['chidon_type']][] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial;
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <?php
        foreach ( $info as $gender => $more ) {
            echo "<h1>" . ucwords( $gender ) . "</h1>";
            ?>
            <table>
                <tr>
                    <th>School</th>
                    <th>Type</th>
                    <th>Is Walking</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>DOB</th>
                    <th>Phone Number</th>
                    <th>Email</th>
                    <th>Sweater Size</th>
                    <th>Accomodation Family</th>
                    <th>Accomodation Address</th>
                    <th>Accomodation Phone Number</th>
                    <th>Has own Vehicle</th>
                </tr>
                <?php
                foreach ( $more as $row ) {
                    echo "<tr><td>" . $row['school_name'] . "</td><td>";
                    switch ( intval($row['chap_type']) ) {
                        case 1:
                            echo "Chaperone";
                        break;
                        case 2:
                            echo "Walking Supervisor";
                        break;
                        case 3:
                            echo "Principal";
                        break;
                        case 4:
                            echo "Other";
                        break;
                    }
                    echo "</td><td>" . ( intval($row['is_walking']) ? 'yes' : 'no') . "</td><td>" . $row['first_name'] . "</td><td>" . $row['last_name'] . "</td><td>" . 
                        $row['dob'] . "</td><td>" . $row['phone'] . "</td><td>" . $row['email'] . "</td><td>" . ( intval($row['sweater']) ? $row['sweater_size'] : 'n/a' ) . 
                        "</td><td>" . $row['acc_name'] . "</td><td>" . $row['acc_address'] . "</td><td>" . $row['acc_phone'] . "</td><td>" . 
                        ( intval($row['vehicle']) ? 'yes' : 'no') . "</td></tr>";
                }
                ?>
            </table>
            <?php
        }
        ?>
    </body>
</html>