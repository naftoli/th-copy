<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

// retrieve all mivtzoim rows from dbs
$sth = $MASHPIA_DB->query("select * from mivtzoim");
$mivtzoim = $sth->fetchAll();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
//echo "<pre>"; print_r( $parshos ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark Mivtzoim</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="mivtzoim.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mark Mivtzoim</h1>

        <form action="mark.php" method="post">
            <select name="school" id="school">
                <option value="0">Select School</option>
                <?php
                foreach ( $schools as $id => $school ) { 
                    echo "<option value='" . $id . "'>" . $school . "</option>";
                }
                ?>
            </select>
            <br /><br />

            <select name="grade"id="grade">
                <option value="0">Select Grade</option>
            </select>
            <br /><br />

            <select name="mivtzoim" id="mivtzoim">
                <option value="0">Select Mivtzoim</option>
                <?php
                foreach ( $mivtzoim as $row ) {
                    echo "<option value='" . $row['mivtzoim_id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
            <br /><br />

            <div id="taskDisplay" style="display: none;">
                <select name="task" id="task"></select>
                <br /><br />
            </div>

            <input type="submit" name="submit" value="Submit" id="submit" class="disabled" disabled />
        </form>
    </body>

    <script>                
        $("#school").change( function() {
            var school = $(this).val();
            $.get('/ajax/getClasses.php?flat=true', { id : school }, function( info ) {
                var grades = $.parseJSON( info );
                var html = "<option value='0'>Choose Grade</option>";
                html += "<option value='-1'>All Grades</option>";
                for (var g in grades) {
                    html += "<option value='" + grades[g][0] + "'>" + grades[g][1] + "</option>";
                }
                $("#grade").empty();
                $("#grade").append( html );
            });
        });

        $("#mivtzoim").change( function() {
            $("#submit").addClass('disabled');
            var id = $(this).val();
            $.post('ajax/mivtzoim.php', { id : id }, function( success ) {
                var mivtzoim = JSON.parse( success );
                console.log( mivtzoim );
                if ( !mivtzoim.error ) {
                    var count = 0; // keep track of how many tasks are actually being output
                    var html = "<option value='0'>All Tasks</option>";
                    for ( var m in mivtzoim.data ) {
                        html += "<option value='" + m +  "'>" + mivtzoim.data[m] + "</option>";
                        count++;
                    }
                    $("#task").empty();
                    $("#task").append( html );
                    if ( count > 1 ) {
                        $("#taskDisplay").show();
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    } else {
                        $("#taskDisplay").hide();
                        var html = "<input type='hidden' name='task' value='" + m + "' />";
                        $("#taskDisplay").after(html);
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    }
                } else {
                    alert( mivtzoim.data );
                    $("#submit").attr('disabled', false);
                    $("#submit").removeClass('disabled');
                }
            });
        });
    </script>
</html>