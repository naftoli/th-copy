<?php
$admin_auth = array('school'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <script
            src="https://code.jquery.com/jquery-1.12.4.min.js"
            integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
            crossorigin="anonymous"></script>
        <style>
            fieldset {
                width: 50%;
                margin: auto;
                padding: 15px;
                border-radius: 20px;
                border: 2px solid #73AD21; 
            }
            legend {
                margin-left: 20px;
            }
        </style>
    </head>
    <body>
        <form method="post" action="slides.php">
            <fieldset>
                <legend>School / Grade</legend>
                School: <select name="school" id="school">
                    <?php
                    foreach ( $schools as $id => $name ) {
                        echo "<option value='" . $id . "|" . $name . "'>" . $name . "</option>";
                    }
                    ?>
                </select><br /><br />
                Grade: <select name="grades" id="grades">
                    <option value='0'>None Selected</option>
                </select>
            </fieldset>
            <br />

            <fieldset>
                <legend>Name Options</legend>
                <input type="radio" name="name" value="en" checked /> Show names of chayolim in english<br />
                <input type="radio" name="name" value="he" /> Show names of chayolim in hebrew
            </fieldset>
            <br />

            <fieldset>
                <legend>Medal Options</legend>
                <input type="radio" name="type" value="1" checked /> Show only current medals earned<br />
                <input type="radio" name="type" value="2" /> Show all medals earned<br />
                <input type="radio" name="type" value="3" /> Show all medals earned but show old medals as greyed out
            </fieldset>
            <br />
            <div align="center">
                <input type="submit" name="submit" />
            </div>
        </form>
    </body>
    <script>
        $("#school").change( function() {
            updateGrades();
        });

        function updateGrades() {
            var school = $("#school").val();
            var pos = school.indexOf('|');
            var id = 0;
            if ( pos > 0 ) {
                id = school.substring(0,pos);
            }
            if ( id > 0 ) {
                $.get("/ajax/getClasses.php", {
                    id: id,
                    flat: false,
                    named: false,
                    extra: ''
                }, function( info ) {
                    var grades = JSON.parse( info );
                    var html = "<option value='-1'>All Grades</option>";
                    for (var g in grades) {
                        var grade = grades[g];
                        html += "<option value='" + grade[0] + "'>" + grade[1] + "</option>";
                    }
                    $("#grades").empty()
                    $("#grades").append( html );
                });
            } else {
                $("#grades").empty();
            }
        }

        $( function() {
            var school = $("#school").val();
            if (school.indexOf('|') !== -1) updateGrades();
        });
    </script>
</html>