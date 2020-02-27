<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id,
        s.school_name,
        chidon_hold_date,
        bus,
        food,
        tcs.airport
    FROM
        schools s
            JOIN
        th_chidon tc USING (school_id)
            LEFT JOIN
        th_chidon_schools tcs USING (school_id, year)
    WHERE
        year = :year
            AND (tc.khk = 1 OR tc.school_rep
            OR tc.trophy_contestant = 1
            OR tc.contestant = 1)
    GROUP BY school_id 
    ORDER BY bus
");
$stmt->execute([':year' => $year]);
$schools = $stmt->fetchAll();

$chaps = $MASHPIA_DB->prepare("
    SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :id AND chap_type = 1
");
$walking = $MASHPIA_DB->prepare("
    SELECT * FROM th_chidon_chaps WHERE year = :year AND school_id = :id AND is_walking = 1
");
$needed = $MASHPIA_DB->prepare("
    SELECT needed FROM th_chidon_chaps_needed WHERE year = :year AND school_id = :id
");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                padding: 5px;
                font-family: Arial;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>School</th>
                <th>Bus</th>
                <th>Airport</th>
                <th>Food for Trip Back</th>
                <th>Hold Placed on CC</th>
                <th>Date of Hold</th>
                <th>Number of Chaps</th>
                <th>Number of Walking Supers (may include chaps)</th>
                <th>Number of Required Supers (may include chaps)</th>
                <th>Number of Missing Supers</th>
            </tr>
            <?php
            foreach ( $schools as $school ) {
                if ( $school['school_id'] == 612 ) continue; // skip unassigned school
                $chaps->execute([
                    ':year' => $year, 
                    ':id'   => $school['school_id']
                ]);
                $rows = $chaps->fetchAll();
                $num_chaps = count( $rows );

                $walking->execute([
                    ':year' => $year, 
                    ':id'   => $school['school_id']
                ]);
                $rows = $walking->fetchAll();
                $num_walking = count( $rows );

                $needed->execute([
                    ':year' => $year, 
                    ':id'   => $school['school_id']
                ]);
                $row = $needed->fetch();
                $num_needed = $row['needed'];

                $num_missing = $num_needed - $num_walking;
                if ( $num_missing < 0 ) $num_missing = 0;

                echo "<tr id=" . $school['school_id'] . "><td>" . $school['school_name'] . "</td><td>";
                echo "<select name='bus' class='bus'>";
                echo "<option value='0' ";
                if (intval( $school['bus'] ) == 0) echo "selected";
                echo ">School Bus</option>";
                echo "<option value='1' ";
                if (intval( $school['bus'] ) == 1) echo "selected";
                echo ">Chidon Bus to Airport</option>";
                echo "<option value='2' ";
                if (intval( $school['bus'] ) == 2) echo "selected";
                echo ">Chidon Bus to CH</option>";
                echo "</td><td>" . $school['airport'] . "</td><td>" . (intval($school['food']) ? 'yes' : 'no') . "</td><td>";
                if ( intval($school['chidon_hold_date']) ) {
                    echo "yes</td><td>" . $school['chidon_hold_date'];
                } else {
                    echo "no</td><td>";
                }
                echo "</td><td>" . $num_chaps . "</td><td>" . $num_walking . "</td><td>" . $num_needed . "</td><td>" . $num_missing . "</td></tr>";
            }
            ?>
        </table>
    </body>
    <script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
    <script>
        $(".bus").change( function() {
            let bus = $(this).val();
            let school = $(this).parent().parent().attr('id');
            $.post('ajax/updateBus.php', { school_id: school, bus: bus }, function( result ) {
                const res = JSON.parse( result );
                if ( res.success ) {
                    alert("Updated.");
                } else {
                    alert("Error updating.");
                }
            });
        });
    </script>
</html>