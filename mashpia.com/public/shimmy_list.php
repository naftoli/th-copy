<?php
require 'db.php';

$info = array();
$sql = "select a.* from admins a 
        join admin_auths aa using (admin_id)
        join users u on (u.user_id = aa.id)
        where aa.role_id = 1
        and aa.auth = 'user'
        group by a.admin_id";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}

$ranks = array();
$sql = "select * from ranks order by rank_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $ranks[$row['rank_ord']] = $row['rank_name'];
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-family: sans-serif;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Charidy List</th>
                <th>Staff List</th>
                <th>Mashpia List</th>
                <th>Charidy ID</th>
                <th>Charidy Donation</th>
                <th>Charidy Donation Date</th>
                <th>Charidy Solicited By</th>
                <th>Charidy First Name</th>
                <th>Charidy Last Name</th>
                <th>Charidy Email</th>
                <th>Charidy Phone</th>
                <th>Charidy Address</th>
                <th>Staff Type</th>
                <th>Staff Name</th>
                <th>Staff Email</th>
                <th>Staff Cell Number</th>
                <th>Parent ID</th>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Father's First Name</th>
                <th>Father's Cell Number</th>
                <th>Mother's First Name</th>
                <th>Mother's Cell Number</th>
                <th>Email Address</th>
                <th>Address</th>
                <th>City</th>
                <th>State</th>
                <th>Country</th>
                <?php for ($i = 0; $i < 15; $i++) : ?>
                    <th>Name of Child</th>
                    <th>School</th>
                    <th>Grade</th>
                <?php endfor; ?>
            </tr>
            <?php
            $year = 5776;
            $charidyEmails = array();
            $staffEmails = array();
            foreach ($info as $row) {
                echo "<tr>";
                // check charidy list
                $charidy = false;
                $sqlCharidy = "select charidy_id, sum(donation) as donation, fname, lname, email, phone, donation_date, solicited_by, address, city, zip, state, country
                                from charidy where year = " . $year . " and email = '" . $row['admin_email'] . "'";
                $resultCharidy = mysql_query($sqlCharidy);
                $rowCharidy = mysql_fetch_assoc($resultCharidy);
                if ($rowCharidy['donation'] > 0) {
                    $charidy = true;
                    $charidyEmails[] = $rowCharidy['email'];
                    echo "<td>&#x2713;</td>";
                } else {
                    echo "<td></td>";
                }
                
                // check staff list
                $staff = false;
                $sqlStaff = "select * from charidy_school_staff where year = " . $year . " and email = '" . $row['admin_email'] . "'";
                $resultStaff = mysql_query($sqlStaff);
                if (mysql_num_rows($resultStaff) > 0) {
                    $staff = true;
                    $staffEmails[] = $row['admin_email'];
                    echo "<td>&#x2713;</td>"; 
                } else {
                    echo "<td></td>";
                }
                
                echo "<td>&#x2713;</td>";
                
                if ($charidy) {
                    echo "<td>" . $rowCharidy['charidy_id'] . "</td><td>" . $rowCharidy['donation'] . "</td><td>" . $rowCharidy['donation_date'] . "</td><td>" .
                        $rowCharidy['solicited_by'] . "</td><td>" . $rowCharidy['fname'] . "</td><td>" . $rowCharidy['lname'] . "</td><td>" . $rowCharidy['email'] .
                        "</td><td>" . $rowCharidy['phone'] . "</td><td>" . $rowCharidy['address'] . "<br />" . $rowCharidy['city'] . ', ' . $rowCharidy['state'] .
                        ' ' . $rowCharidy['zip'] . "<br />" . $rowCharidy['country'] . "</td>";
                } else {
                    echo "<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                }
                
                if ($staff) {
                    $rowStaff = mysql_fetch_assoc($resultStaff);
                    echo "<td>" . $rowStaff['staff_type'] . "</td><td>" . $rowStaff['staff_name'] . "</td><td>" . $rowStaff['email'] . "</td><td>" . $rowStaff['cell_number'] . "</td>";
                } else {
                    echo "<td></td><td></td><td></td><td></td>";
                }
                
                echo "<td>" . $row['admin_id'] . "</td><td>" . $row['last'] . "</td><td>" . $row['first'] . "</td><td>" .
                    $row['father'] . "</td><td>" . $row['admin_phone_mobile'] . "</td><td>" . $row['mother'] . "</td><td>" .
                    $row['admin_phone_mobile2'] . "</td><td>" . $row['admin_email'] . "</td><td>" . $row['admin_address1'] . "</td><td>" .
                    $row['admin_city'] . "</td><td>" . $row['admin_state'] . "</td><td>" . $row['admin_country'] . "</td>";
                $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name from users u
                        left join schools s using (school_id)
                        left join classes c on c.class_id = u.class_id
                        join admin_auths aa on aa.id = u.user_id 
                        where aa.admin_id = " . $row['admin_id'];
                $result = mysql_query($sql) or die(mysql_error());
                $i = 0;
                while ($user = mysql_fetch_assoc($result)) {
                    // get rank
                    $sqlRank = "select rank_ord from rank_marks where user_id = " . $user['user_id'] . " order by rank_ord desc limit 1";
                    $resultRank = mysql_query($sqlRank) or die(mysql_error());
                    $rowRank = mysql_fetch_assoc($resultRank);
                    $rank = $rowRank['rank_ord'] ? $rowRank['rank_ord'] : 1;
                    echo "<td>" . $ranks[$rank] . ' ' . $user['first'] . ' ' . $user['last'] . "</td><td>" . $user['school_name'] . "</td><td>" .
                        $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']) . "</td>";
                    $i++;
                }
                for (; $i < 15; $i++) {
                    echo "<td></td>";
                }
                echo "</tr>";
            }
            
            $sql = "select * from charidy where year = " . $year . " and email not in ('" .
                implode('\',\'', $charidyEmails) . "')";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                echo "<tr><td>&#x2713;</td><td></td><td></td><td>" . $row['charidy_id'] . "</td><td>" . $row['donation'] . "</td><td>" . $row['donation_date'] . "</td><td>" .
                    $row['solicited_by'] . "</td><td>" . $row['fname'] . "</td><td>" . $row['lname'] . "</td><td>" . $row['email'] . "</td><td>" . $row['phone'] . "</td><td>" . 
                    $row['address'] . "<br />" . $row['city'] . ', ' . $row['state'] . ' ' . $row['zip'] . "<br />" . $row['country'] . "</td>";
                for ($j = 0; $j < 31; $j++) echo "<td></td>";
                echo "</tr>";
            }
            
            $sql = "select * from charidy_school_staff where year = " . $year . " and email not in ('" .
                implode('\',\'', $staffEmails) . "')";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                echo "<tr><td></td><td>&#x2713;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
                echo "<td>" . $row['staff_type'] . "</td><td>" . $row['staff_name'] . "</td><td>" . $row['email'] . "</td><td>" . $row['cell_number'] . "</td>";
                for ($j = 0; $j < 27; $j++) echo "<td></td>";
            }
            ?>
        </table>
    </body>
</html>
