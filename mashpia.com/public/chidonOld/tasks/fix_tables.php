<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

function entryExists($table, $column, $value) {
    $sql = "select * from mashpia_chidon." . $table . " where " . $column . " = " . $value;
    $result = mysql_query($sql);
    return mysql_num_rows($result);
}

$tables = [];
$skip = ['wp_antw_cf7_vdata_entry', 'wp_antw_wp_pro_quiz_statistic', 'wp_antw_wc_admin_note_actions'];
$sql = "show tables from mashpia_chidon_old";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $table = $row['Tables_in_mashpia_chidon_old'];
    if (in_array($table, $skip)) continue;
    $tables[] = $table;
}

$columns = [];
foreach ($tables as $table) {
    $sql = "show columns from mashpia_chidon_old." . $table;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $columns[$table][] = $row['Field'];
    }
}

$info = [];
foreach ($tables as $table) {
    $sql = "select * from mashpia_chidon_old." . $table;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$table][] = $row;
    }
}

$qrys = [];
foreach ($tables as $table) {
    $fields = $columns[$table];
    $numFields = count($fields);
    foreach ($info[$table] as $row) {
        // find out if row exists
       if (! entryExists($table, $fields[0], $row[$fields[0]])) {
           $sql = "insert ignore into mashpia_chidon." . $table . " set ";
           foreach ($fields as $idx => $field) {
               $sql .= $field . " = '" . str_replace("'", "\'", $row[$fields[$idx]]) . "'";
               if ($idx < ($numFields - 1)) $sql .= ", ";
           }
           $qrys[$table][] = $sql;
       }
    }
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ($tables as $table) {
    foreach ($qrys[$table] as $qry) {
        if (!mysql_query($qry)) {
            echo $qry;
            echo "<br />" . mysql_error();
            $success = false;
            break 2;
        }
    }
}
if ($success) {
    mysql_query('commit');
    echo "done.";
} else {
    mysql_query('rollback');
    echo "errors.";
}
mysql_query('set autocommit=1');