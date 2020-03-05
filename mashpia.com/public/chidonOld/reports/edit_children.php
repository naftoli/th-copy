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

$fields = [];
$stmt = $MASHPIA_DB->query("show columns from th_chidon");
foreach ( $stmt->fetchAll() as $row ) {
    if ( !in_array( $row['Field'], ['grade'] ) ) $fields[] = $row['Field'];
}

if ( isset( $_POST['submit'] ) && !isset( $_POST['chidon_ids'] ) ) {
    // echo "<pre>"; print_r( $_POST ); echo "</pre>"; exit;
    $qrys = [];
    $info = [];
    // arrange info so that the first index is the user id and the next index is the field
    foreach ( $_POST as $k => $v ) {
        if ( is_array( $v ) ) {
            foreach ( $v as $user_id => $val ) {
                $info[$user_id][$k] = $val;
            }
        }
    }
    // echo "<pre>"; print_r( $info ); echo "</pre>";
    foreach ( $info as $user_id => $chidon ) {
        $qry = "update th_chidon set ";
        foreach ( $fields as $field ) {
            if ( isset( $chidon[$field] ) ) {
                if ( is_numeric( $chidon[$field] ) ) $qry .= $field . " = " . mysql_real_escape_string( $chidon[$field] ) . ", ";
                else $qry .= $field . " = \"" . mysql_real_escape_string( $chidon[$field] ) . "\", ";
            }
        }
        $qry = substr( $qry, 0, strlen($qry) - 2 );
        $qry .= " where user_id = " . $user_id . " and year = " . $year;
        $qrys[] = $qry;
    }
    // echo "<pre>"; print_r( $qrys ); echo "</pre>";
    // exit;
    foreach ( $qrys as $qry ) mysql_query( $qry ) or die( mysql_error() . "<br />" . $qry );
    $msg = "Saved.";
} else if ( !empty( $_POST['chidon_ids'] ) ) {
    $ids = explode(";", $_POST['chidon_ids']);
    $ids = implode(',', str_replace(' ', '', $ids));
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM th_chidon WHERE year = :year AND (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1) AND th_chidon_id in ($ids)
    ");
    // $stmt->bindValue(':year', $year, PDO::PARAM_INT);
    // $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    // $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute([':year' => $year]);
    // echo $stmt->debugDumpParams();
    $rows = $stmt->fetchAll();
    if ( empty( $rows ) ) {
        $msg = "No children found with those IDs.";
    }
}

// $start = 0;
// $limit = 50;
// if ( isset( $_POST['start'] ) ) $start = $_POST['start'] + $limit;

// $stmt = $MASHPIA_DB->prepare("
//     SELECT * FROM th_chidon WHERE year = :year AND (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1) LIMIT :start, :limit
// ");
// $stmt->bindValue(':year', $year, PDO::PARAM_INT);
// $stmt->bindValue(':start', $start, PDO::PARAM_INT);
// $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
// $stmt->execute();
// // echo $stmt->debugDumpParams();
// $rows = $stmt->fetchAll();

$stmt = $MASHPIA_DB->prepare("
    SELECT 
        user_id, first, last 
    FROM 
        users u 
    JOIN
        th_chidon tc USING (user_id) 
    WHERE
        year = :year AND (tc.khk = 1 or tc.school_rep or tc.trophy_contestant = 1 or tc.contestant = 1)
    ORDER BY 
        last, first
");
$stmt->execute([':year' => $year]);
$users = $stmt->fetchAll();
?>
<DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            table {
                float: left;
            }
            tr, th, td {
                font-family: Arial;
                font-size: 14px;
                padding: 5px;
            }
            td {
                height: 30px;
            }
        </style>
    </head>
    <body>        
        <form action="edit_children.php" method="post">
            <?php if ( !isset( $rows ) || empty( $rows ) ) : ?>
                <?php 
                if ( isset( $msg ) ) {
                    echo "<div style='color: red;'>" . $msg . "</div><br />";
                }
                ?>
                Enter chidon ID's separated by semicolon: <input type="text" name="chidon_ids" /><br /><br />
                <input type="submit" name="submit" value="go" />
            <?php else : ?>
            <!-- <input type="hidden" name="start" value="<?= $start ?>" /> -->
                <button name='submit'>Save</button><br /><br />
                <?php
                echo "<table>";
                foreach ( $fields as $i => $field ) {
                    echo "<tr><td>" . $field . "</td></tr>";
                }
                echo "</table>";
                foreach ( $rows as $row ) {
                    echo "<table>";
                    foreach ( $fields as $i => $field ) {
                        if ( $i < 4 ) echo "<tr><td>" . $row[$field] . "</td></tr>";
                        else echo "<tr><td><input type='text' class='" . $field . "' name='" . $field . "[" . $row['user_id'] . "]' value='" . $row[$field] . "' /></td></tr>";
                    }
                    echo "</table>";
                }
                ?>
            <?php endif; ?>
        </form>
    </body>
    <script
        src="https://code.jquery.com/jquery-1.12.4.min.js"
        integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
        crossorigin="anonymous"></script>
    <script>
        $(function() {
            $(".host_street_num").blur( function() {
                const number = $(this).val();
                const street = $(this).parent().parent().prev().find('.host_street').val();
                $.post("../chidon_drive/ajax/getCrossStreets.php", { street: street, num: number }, function( result ) {
                    const res = JSON.parse( result );
                    console.log( res );
                    if ( res.error ) alert("There's no such address in our system. Either the Street Number or Street Name are incorrect.");
                    else {
                        $(this).parent().parent().prev().find(".walking_zone").val( res.data.zone_5780 );
                        $(this).parent().parent().prev().find(".between_streets1").val( res.data.cross1 );
                        $(this).parent().parent().prev().find(".between_streets2").val( res.data.cross2 );
                        alert("Walking zone updated. Don't forget to Save.");
                    }
                });
            }); 
        });           
    </script>
</html>