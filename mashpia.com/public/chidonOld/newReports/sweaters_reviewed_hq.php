<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$schools = [];
$sql = "select school_name from schools where sweaters_confirmed_5782 = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $schools[] = $row['school_name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>School Sweaters Reviewed</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>School Sweaters Reviewed</h1>
Below is the list of schools that have confirmed that they reviewed their sweater shipment.<br /><br />
<?php
    if (empty($schools)) echo "No schools have confirmed yet.";
    else {
        echo "<div style='font-size: 14px;'>";
        echo "<ul>";
        foreach ($schools as $school) echo "<li>" . $school . "</li>";
        echo "</ul>";
        echo "</div>";
    }
?>
</body>
</html>