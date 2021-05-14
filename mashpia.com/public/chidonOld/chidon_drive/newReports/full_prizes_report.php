<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$prizes = [];

$sql = "select *, tcz.paid as paidUp from th_chidon tc 
        join th_chidon_zelda tcz on tc.parent_id = tcz.admin_id 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where tc.paid > 0  
        and tc.year = " . $year . " 
        order by s.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $school = $row['school_name'];
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $info[$school][$grade][] = $row;

    // get prizes
    $sqlPrize = "select * from chidon_user_prizes cup join chidon_prizes cp using (prize_id) where user_id = " . $row['user_id'];
    $resultPrize = mysql_query($sqlPrize);
    while ($rowPrize = mysql_fetch_assoc($resultPrize)) {
        $prizes[$rowPrize['user_id']][] = $rowPrize;
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Prizes Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 10px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
        <h1>Prizes Report</h1>
        <table>
            <tr>
                <th>Admin ID</th>
                <th>Chidon ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>School</th>
                <th>Grade</th>
                <th>Eligibility</th>
                <th>Registration Fee</th>
                <th>Amount Paid</th>
                <th>Chidon Drive</th>
                <th>Voucher</th>
                <th>Balance</th>
                <th>Yarmulka Size</th>
                <th>Chidon Bracelet</th>
                <th>Total Prize Value</th>
                <?php
                for ($i = 1; $i < 9; $i++) {
                    echo "<th>Prize # " . $i . "</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($info as $school => $more) {
                foreach ($more as $grade => $other) {
                    foreach ($other as $row) {
                        echo "<tr><td>" . $row['admin_id'] . "</td><td>" . $row['th_chidon_id'] . "</td><td>" .
                            $row['first'] . "</td><td>" . $row['last'] . "</td><td>";
                        if ($row['gender'] == 'M') echo "male";
                        else if ($row['gender'] == 'F') echo "female";
                        echo "</td><td>" . $school . "</td><td>" . $grade . "</td><td>";
                        if (intval($row['shabbaton_maven'])) echo "Sweater";
                        else if (intval($row['shabbaton_pro'])) echo "Sweater and Gifts";
                        else if (intval($row['shabbaton_expert'])) echo "Prizes and Trips";
                        else if (intval($row['shabbaton_trophy'])) echo "Prizes and Trips";
                        echo "</td><td>" . $row['reg_fee'] . "</td><td>" . $row['paidUp'] . "</td><td>" . $row['chidon_drive'] .
                            "</td><td>" . $row['coupon'] . "</td><td>" . $row['balance'] . "</td><td>";
                        if ($row['gender'] == 'M') echo $row['yarmulka'] . "</td><td>No</td><td>";
                        else if ($row['gender'] == 'F' && !intval($row['shabbaton_maven'])) echo "</td><td>Yes</td><td>";
                        else echo "</td><td>No</td><td>";

                        // prizes section
                        $total = 0;
                        $numPrizes = 0;
                        if (isset($prizes[$row['user_id']])) {
                            $numPrizes = count($prizes[$row['user_id']]);
                            foreach ($prizes[$row['user_id']] as $prize) {
                                $total += intval($prize['price']);
                            }
                        }
                        echo $total . "</td>";
                        for ($i = 1; $i < 9; $i++) {
                            if (isset($prizes[$row['user_id']][$i-1])) {
                                $prize = $prizes[$row['user_id']][$i-1];
                                $name = $prize['prize_name'];
                                if ($prize['color']) $name .= " - Color: " . $prize['color'];
                                if ($prize['size']) $name .= "; Size: " . $prize['size'];
                                echo "<td>" . $name . "</td>";
                            } else {
                                echo "<td></td>";
                            }
                        }
                        echo "</tr>";
                    }
                }
            }
            ?>
        </table>
    </body>
</html>
