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
    $sql = "select * from mashpia_chidon.wp_antw_" . $table . " where " . $column . " = " . $value;
    $result = mysql_query($sql);
    return mysql_num_rows($result);
}

$tables = ['postmeta', 'posts', 'term_relationships', 'usermeta'];

$columns = [];
foreach ($tables as $table) {
    $sql = "show columns from mashpia_chidon_old.wp_antw_" . $table;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $columns[$table][] = $row['Field'];
    }
}

$info = [];
foreach ($tables as $table) {
    $sql = "select * from mashpia_chidon_old.wp_antw_" . $table;
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
           $sql = "insert into mashpia_chidon.wp_antw_" . $table . " set ";
           foreach ($fields as $idx => $field) {
               $sql .= $field . " = \"" . $row[$fields[$idx]] . "\"";
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