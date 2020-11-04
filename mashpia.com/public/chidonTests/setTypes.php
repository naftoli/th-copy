<?php
//ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

if (isset($_POST['submit'])) {
    $ct->setTestTypes($_POST['type']);
}

$info = [];
foreach ($schools as $id => $school) {
    $ct->setStudents($id);
    $info[$id] = $ct->getStudents();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Set Chidon Test Type</title>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
            td:not(.type) {
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Chidon Test Types</h1>
        <?php
        $types = ['maven', 'pro', 'expert'];
        echo "<form action='' method='post'>";
        echo "<input type='submit' name='submit' value='Save' />";
        foreach ($info as $school => $children) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table><tr><th>Chidon ID</th><th>Grade</th><th>Student</th><th>Test Type</th></tr>";
            foreach ($children as $child) {
                $grade = $child['class_grade'] . ($child['class_sub'] ? '' : '-' . $child['class_sub']);
                $name = $child['first'] . ' ' . $child['last'];
                $id = $child['th_chidon_id'];
                echo "<tr><td>" . $id . "</td><td>" . $grade . "</td><td>" . $name . "</td><td class='type'>";
                foreach ($types as $type) {
                    echo "<input type='radio' name='type[" . $child['th_chidon_id'] . "]' value='" . $type . "'";
                    if ($child['test_type'] == $type) echo " checked";
                    echo " />" . ucwords($type) . "<br />";
                }
                echo "</td></tr>";
            }
            echo "</table>";
        }
        echo "<input type='submit' name='submit' value='Save' />";
        echo "</form>";
        ?>
    </body>
</html>
