<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$info = [];
$sql = "
    SELECT 
        s.school_name,
        c.class_grade,
        c.class_sub,
        u.first,
        u.last,
        tc.th_chidon_id,
        tc.year as YEAR,
        y.*
    FROM
        th_chidon tc
            JOIN
        users u USING (user_id)
            LEFT JOIN
        yahadus_book_purchases y USING (user_id , year)
            JOIN
        schools s ON s.school_id = u.school_id
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year IN (5780 , 5781)
    ORDER BY school_name , class_grade , class_sub , last , first , tc.year
";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Book Report</title>
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 5px;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th>Chidon ID</th>
            <th>School</th>
            <th>Class</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>At Event</th>
            <th>From Store</th>
            <th>From School</th>
            <th>From Parent Acct</th>
            <th>Other</th>
            <th>Year</th>
        </tr>
        <?php
        foreach ($info as $row) {
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_grade']);
            echo "<tr><td>" . $row['th_chidon_id'] . "</td><td>" . $row['school_name'] . "</td><td>" . $grade . "</td><td>" .
                $row['first'] . "</td><td>" . $row['last'] . "</td>";
            switch ($row['location']) {
                case 'event':
                    echo "<td>Yes</td><td></td><td></td><td></td><td></td>";
                    break;
                case 'store':
                    echo "<td></td><td>" . $row['store_name'] . ", " . $row['store_city'] . "</td><td></td><td></td><td></td>";
                    break;
                case 'parent_account':
                    echo "<td></td><td></td><td></td><td>Yes</td><td></td>";
                    break;
                case 'school':
                    echo "<td></td><td></td><td>Yes</td><td></td><td></td>";
                    break;
                default:
                    echo "<td></td><td></td><td></td><td></td><td>Yes</td>";
                    break;
            }
            echo "<td>" . $row['YEAR'] . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>
