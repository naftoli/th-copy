<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonRegYear();

$info = [];
$sql = "
    SELECT 
        school_id,
        school_name,
        first,
        last,
        location,
        store_name,
        store_city,
        yahadus_book_purchase_id
    FROM
        yahadus_book_purchases
            JOIN
        users USING (user_id)
            JOIN
        schools USING (school_id)
    WHERE
        year = $year AND school_id IN (" . implode(',', array_keys($schools)) . ")
    ORDER BY school_name , last , first
    ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Yahadus Book Questionnaire</title>
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
<h1>Yahadus Book Questionnaire <?= $year ?></h1>
<table>
    <tr>
        <th>School</th>
        <th>Student</th>
        <th>Location</th>
        <th>Store</th>
    </tr>
    <?php
    foreach ($info as $row) {
        echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" .
            ucwords(str_replace('_', ' ', $row['location'])) .
            "</td><td>" . $row['store_name'] . "<br />" . $row['store_city'] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
