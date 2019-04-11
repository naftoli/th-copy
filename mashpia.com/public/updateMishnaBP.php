<?php
require_once 'db.php';

$marks = [];
$sql = "select dtmm.user_id, dtmm.done_qty, u.school_id, u.class_id from date_tasks_marks dtmm
        join date_tasks dt using (date_task_id) 
        join users u using (user_id) 
        where dt.grid_id = 21014";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $marks[] = $row;
}

$qrys = [];
foreach ( $marks as $row ) {
  $qrys[] = "insert into lines_learned 
              set campaign_id = 14, 
              school_id = " . $row['school_id'] . ", 
              user_id = " . $row['user_id'] . ", 
              class_id = " . $row['class_id'] . ", 
              mission_sheet_amount = " . $row['done_qty'];
}

//echo "<pre>"; print_r( $qrys ); echo "</pre>";
mysql_query('set autocommit=0');
mysql_query('begin');

$success = true;
foreach ( $qrys as $qry ) {
  if ( !mysql_query( $qry ) ) {
    $success = false;
    break;
  }
}

if ( $success ) {
  echo "done.";
  mysql_query('commit');
} else {
  echo "error.<br />" . $qry;
  mysql_query('rollback');
}
mysql_query('set autocommit=1');