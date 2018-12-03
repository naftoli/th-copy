<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.parshos.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// retrieve all mivtzoim rows from dbs
$sth = $MASHPIA_DB->query("select * from mivtzoim");
$mivtzoim = $sth->fetchAll();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$parshos = Parshos::getParshos( GlobalSettings::getCurrentYear() );
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

            <div id="parshaDisplay" style="display: none;">
                <select name="parsha" id="parsha">

                </select>
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
                if ( mivtzoim ) {
                    var html = "<option value='0'>Choose Parsha</option>";
                    for ( var m in mivtzoim ) {
                        var mivtza = mivtzoim[m];
                        var parshos = <?= json_encode( $parshos ); ?>;
                        var count = 0; // keep track of how many weeks are actually being output
                        for ( var p in parshos ) {
                            var parsha = parshos[p];
                            // only show relevant parshos
                            if ( mivtza.start > parsha.end ) continue;
                            if ( mivtza.end < parsha.start ) continue;
                            html += "<option value='" + parsha.start + '|' + parsha.end + '|' + parsha.name +  "'>" + parsha.name + "</option>";
                            count++;
                        }
                    }
                    $("#parsha").empty();
                    $("#parsha").append( html );
                    if ( count > 1 ) {
                        $("#parshaDisplay").show();
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    } else {
                        $("#parshaDisplay").hide();
                        var html = "<input type='hidden' name='parsha' value='" + parsha.start + '|' + parsha.end + '|' + parsha.name + "' />";
                        $("#parshaDisplay").after(html);
                        $("#submit").attr('disabled', false);
                        $("#submit").removeClass('disabled');
                    }
                }
            });
        });
    </script>
</html>