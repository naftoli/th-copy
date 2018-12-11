<?php
$admin_auth = array('school'); 
require('header.php');

require 'class.parshos.php';
$p = new Parshos();
$parshos = $p->getParshos();
$oldParshos = $p->getParshosByYear( 5777 );

$grades = array();
$sql = "select * from classes where class_era = 0 and school_id = " . $admin_user['auths']['school'][0];
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Class Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            fieldset {
                float: left;
                width: 50%;
            }
        </style>
    </head>
    
    <body>
        <?php include('admin_header.php'); ?>
        <h1>Class Report</h1>
        
        <form action="easytable/weekly.php" method="post">
            <p>Use the following options to generate your report.</p>
            <fieldset>
                <legend>Parsha</legend>
                <select name='from' id="from">
                    <option value='0'>Choose Parsha</option>
                    <?php
                    foreach ($oldParshos as $p) {
                        echo "<option value='" . $p['start'] . "'>" . $p['name'] . " - 5777</option>";
                    }
                    foreach ($parshos as $parsha) {
                        echo "<option value='" . $parsha['start'] . "'>" . $parsha['name'] . " - 5778</option>";
                    }
                    ?>
                </select>
            </fieldset>
            <fieldset>
                <legend>Classes</legend>
                <select name="grade[]" id="grade" class="grade" multiple>
                    <option value='0'>Choose Class</option>
                    <?php
                    foreach ($grades as $id => $grade) {
                        echo "<option value='" . $id . "'>" . $grade . "</option>";
                    }
                    ?>
                </select>
            </fieldset>
            <fieldset id="types">
                <legend>Options</legend>
                <input type="checkbox" name="hoo" /> HOO<br />
                <input type="checkbox" name="fc" /> Friendship Circle<br />
                <input type="checkbox" name="personal" /> Personal<br />
            </fieldset>
            <fieldset id="data">
                <legend>Data</legend>
                <input type="checkbox" name="points" /> Points<br />
                <input type="checkbox" name="minutes" /> Minutes<br />
            </fieldset>
            <fieldset>
                <legend>Personal Info</legend>
                <input type="checkbox" name="emails" /> Emails<br />
                <input type="checkbox" name="numbers" /> Phone Numbers<br />
            </fieldset>
            <fieldset>
                <legend>Totals</legend>
                <input type="radio" name="totals" value="1" checked /> Show Totals<br />
                <input type="radio" name="totals" value="0" /> Do NOT Show Totals<br />
                <input type="radio" name="totals" value="2" /> ONLY Show Totals<br />
            </fieldset>
            <!--
            <fieldset id="sortBy">
                <legend>Sort By</legend>
                <input type="radio" name="sortby" value="classes" checked /> Classes<br />
                <input type="radio" name="sortby" value="visits" /> Total Visits<br />
                <input type="radio" name="sortby" value="points" /> Total Points<br />
                <input type="radio" name="sortby" value="points" /> Total Minutes<br />
            </fieldset>
            -->
            <div style="clear: both;"></div>
            <br />
            <div style="margin-left: 8%;">
                <input type="submit" name="submit" value="submit" id="submit" />
            </div>
        </form>
    </body>
    
    <script src="scripts/jquery.min.js"></script>
    <script src="scripts/jquery.styleselect.js"></script>
    <script>
        $(function() {
            $("select").not('.grade').sSelect();
            $("#submit").click(function() {
                var errors = [];
                
                var from = parseInt($("#from").val());
                if (from == 0) {
                    errors.push('You must choose the parsha that you want on the report.\n');
                }
                
                var grade = parseInt($("#grade").val());
                if (grade == 0) {
                    errors.push('You must choose a class.');
                }
                
                var types = false;
                for (var i = 0; i < 3; i++) {
                    if ($("#types input").eq(i).is(":checked")) {
                        types = true;
                        break;
                    }
                }
                if (!types) {
                    errors.push('You must select at least one of the "Options" section.\n');
                }
                
                var data = false;
                for (var i = 0; i < 2; i++) {
                    if ($("#data input").eq(i).is(":checked")) {
                        data = true;
                        break;
                    }
                }
                if (!data) {
                    errors.push('You must select at least one of the "Data" section.\n');
                } 
                                
                if (errors.length) {
                    alert(errors);
                    return false;
                } else {
                    return true;
                }
            });
        });
    </script>
</html>