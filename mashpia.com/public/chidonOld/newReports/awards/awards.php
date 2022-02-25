<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

if (isset($_POST['submit'])) {
    require 'createReport.php';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Awards Report</title>
        <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            fieldset {
                width: 200px;
                border: 1px solid grey;
                border-radius: 15px;
                padding: 15px;
            }
            legend {
                left: 30px;
                padding-left: 5px;
                padding-right: 5px;
            }
            #submit {
                padding: 10px;
                font-size: 16px;
            }
            tr, th, td {
                padding: 5px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Awards Report</h1>
        <?php if (! isset($_POST['submit'])) : ?>
        <div id="instructions">
            Please select the options you want for the report.<br /><br />
            <form action="" method="post" id="awardForm">
                <fieldset>
                    <legend>Type of Award</legend>
                    <input type="radio" name="award_type" class="award_type" value="cert" /> Certificates<br />
                    <input type="radio" name="award_type" class="award_type" value="plaque" /> Plaques<br />
                    <input type="radio" name="award_type" class="award_type" value="medal" /> Medals<br />
                    <input type="radio" name="award_type" class="award_type" value="trophy" /> Trophies<br />
                </fieldset>
                <br />
                <fieldset>
                    <legend>Finals</legend>
                    <input type="radio" name="final" class="final" value="before" /> Before Final<br />
                    <input type="radio" name="final" class="final" value="after" /> After Final<br />
                </fieldset>
                <br />
                <input type="submit" name="submit" id="submit" value="Create Report" />
            </form>
        </div>
        <?php else: ?>
        <div>
            <table>
                <tr>
                    <th>School Name</th>
                    <th>Serial Number</th>
                    <th>Full Hebrew Name</th>
                    <th>Code</th>
                    <th>Template Code</th>
                    <?php if ($_POST['final'] == 'after') : ?>
                    <th>Changes</th>
                    <?php endif; ?>
                </tr>
                <?php
                $i = 0;
                $previousSchool = '';
                $previousGrade = '';
                foreach ($info as $row) {
                    $school = $row['school_name'];
                    $serial = $row['user_serial'];
                    $he_name = $row['first_he'] . ' ' . $row['last_he'];
                    $template = '';
                    if ($row['gender'] == 'M') {
                        $template = 'B-' . $row['class_grade'];
                    } else if ($row['gender'] == 'F') {
                        $template = 'G-' . $row['class_grade'];
                    }
                    $code = '';
                    if (in_array($row['school_id'], [61, 269])) {
                        // find number of child in admins array
                        $key = array_search($row['user_id'], $admins[$row['admin_id']]);
                        $code = $row['admin_id'] . '-' . ($key + 1);
                    } else {
//                        $grade = $row['school_id'] . '-' . $row['class_grade'] . '-' . $row['class_sub'];
                        $grade = $row['school_id'] . '-' . $row['class_grade'];
                        if ($previousGrade != $grade && $previousSchool == $school) {
                            $i = 1;
                            $previousGrade = $grade;
                        }
                        $code = $row['school_id'] . '-' . $row['class_grade'] . '-' . $i;
//                        echo $grade . '-' . $i . "<br />";
                        $i++;
                    }
                    if ($previousSchool != $school) {
                        $colspan = $_POST['final'] == 'after' ? 6 : 5;
                        echo "<tr><td>" . $school . "</td><td colspan='$colspan'></td></tr>";
                        $previousSchool = $school;
                    } else {
                        echo "<tr><td></td><td>" . $serial . "</td><td>" . $he_name . "</td><td>" .
                            $code . "</td><td>" . $template . "</td>";
                        if ($_POST['final'] == 'after') {
                            echo "<td></td>";
                        }
                        echo "</tr>";
                    }
                }
                ?>
            </table>
        </div>
        <?php endif; ?>
    </body>
    <script>
        $("#awardForm").submit( function(e) {
            let type = $(".award_type").is(":checked")
            let final = $(".final").is(":checked")
            if (! (type && final)) {
                alert('You must make a choice in both sections.')
                e.preventDefault()
            }
        })
    </script>
</html>
