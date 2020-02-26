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

if ( isset( $_POST['submit'] ) ) {
    $qrys = [];
    $info = [];
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
}

$start = 0;
$limit = 50;
if ( isset( $_POST['start'] ) ) $start = $_POST['start'] + $limit;

$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM th_chidon WHERE year = :year AND (khk = 1 or school_rep = 1 or trophy_contestant = 1 or contestant = 1) LIMIT :start, :limit
");
$stmt->bindValue(':year', $year, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
// echo $stmt->debugDumpParams();
$rows = $stmt->fetchAll();
?>
<DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            tr, th, td {
                font-family: Arial;
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <form action="edit_child_info.php" method="post">
            <input type="hidden" name="start" value="<?= $start ?>" />
            <button name='submit'>Save & Next 50 >></button><br /><br />
            <table>
                <tr>
                    <?php foreach ( $fields as $field ) : ?>
                        <th><?= $field ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php
                foreach ( $rows as $row ) {
                    echo "<tr>";
                    foreach ( $fields as $i => $field ) {
                        if ( $i < 4 ) echo "<td>" . $row[$field] . "</td>";
                        else echo "<td><input type='text' name='" . $field . "[" . $row['user_id'] . "]' value='" . $row[$field] . "' /></td>";
                    }
                    echo "</tr>";
                }
                ?>
            </table>
        </form>
    </body>
</html>