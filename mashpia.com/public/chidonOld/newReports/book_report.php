<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = $_REQUEST['year'] ?? GlobalSettings::getChidonRegYear();

$info = [];
$sql = "
    SELECT 
        school_id,
        school_name,
        first,
        last,
        user_serial, 
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
<div>
    Choose Year:
    <select name="year" id="year">
        <?php
        for ($i = 0; $i < 4; $i++) {
            $yr = $year - $i;
            echo "<option value='" . $yr . "'";
            if ($yr == $year) echo " selected ";
            echo ">" . $yr . "</option>";
        }
        ?>
    </select>
</div>
<br />
<table>
    <tr>
        <th>Serial Number</th>
        <th>School</th>
        <th>Student</th>
        <th>Location</th>
        <th>Store</th>
    </tr>
    <?php
    foreach ($info as $row) {
        echo "<tr><td>" . $row['user_serial'] . "</td><td>" . $row['school_name'] . "</td><td>" .
            $row['first'] . ' ' . $row['last'] . "</td><td>" .
            ucwords(str_replace('_', ' ', $row['location'])) .
            "</td><td>" . $row['store_name'] . "<br />" . $row['store_city'] . "</td></tr>";
    }
    ?>
</table>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<script>
    $("#year").change( function () {
        let yr = $(this).val()
        location.href = "book_report.php?year=" + yr
    })
</script>
</html>
