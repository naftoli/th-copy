<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$qrys = [];
$sql = "select * from mashpia_backup.rank_marks where date_printed > '2023-10-07'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if ($row['user_id']) {
        $sql = "insert ignore into rank_marks 
                set rank_ord = " . $row['rank_ord'] . ", 
                user_id = " . $row['user_id'] . ", 
                date_promoted = " . $row['date_promoted'];
        if ($row['date_printed']) $sql .= ", date_printed = '" . $row['date_printed'] . "'";
        if ($row['date_book_shipped']) $sql .= ", date_book_shipped = '" . $row['date_book_shipped'] . "'";
        if ($row['date_book_received']) $sql .= ", date_book_received = '" . $row['date_book_received'] . "'";
        if ($row['date_card_shipped']) $sql .= ", date_card_shipped = '" . $row['date_card_shipped'] . "'";
        if ($row['date_card_received']) $sql .= ", date_card_received = '" . $row['date_card_received'] . "'";
        if ($row['ranks_updated']) $sql .= ", ranks_updated = " . $row['ranks_updated'];
        $qrys[] = $sql;
    }
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('start transaction');

foreach ($qrys as $qry) {
    if (! mysql_query($qry)) {
        $success = false;
        echo $qry . "<br />" . mysql_error() . "<br /><br />";
        break;
    }
}

if ($success) {
    mysql_query('commit');
} else {
    mysql_query('rollback');
}
mysql_query('set autocommit=1');

echo "Success: " . $success;