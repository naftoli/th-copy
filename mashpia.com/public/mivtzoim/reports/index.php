<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$mivtzoim = Mivtzoim::getAll();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtzoim Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="../mivtzoim.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtzoim Leaderboard</h1>

        <form id="formSelection">
            <select name="mivtzoim_id" id="mivtzoim_id"> 
                <option value='0'>Choose Mivtzoim for Report</option>
                <?php
                foreach ( $mivtzoim as $row ) {
                    echo "<option value='" . $row['mivtzoim_id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            <select>
            <br /><br />
            <select name="type" id="type">
                <option value="1">School Leaderboard</option>
                <option value="2">Individual Leaderboard</option>
            </select>
            <br /><br />
            <div id="schoolSelection" style="display:none">
                <select name="school" id="school">
                    <option value="0">Select School</option>
                    <?php
                    if ( $admin_user['auth'] == 'super' ) echo "<option value='-1'>All Schools</option>";
                    foreach ( $schools as $id => $school ) {
                        echo "<option value='" . $id . "'>" . $school . "</option>";
                    }
                    ?>
                </select>
                <br /><br />
            </div>
            <input type="submit" name="submit" value="submit" />
        </form>
    </body>
    <script>
        $(function() {
            $("#type").change( function() {
                if ( $(this).val() == 2 ) {
                    $("#schoolSelection").show();
                } else {
                    $("#schoolSelection").hide();
                }
            });

            $("#formSelection").submit( function( e ) {
                e.preventDefault();
                var id = $("#mivtzoim_id").val();
                var type = $("#type").val();
                if ( id > 0 ) {
                    switch ( type ) {
                        case '1':
                            location.href = 'report.php?id=' + id;
                            break;
                        case '2':
                            location.href = 'details.php?id=' + id + '&school=' + $("#school").val();
                            break;
                    }
                }
            });
        });
    </script>
</html>