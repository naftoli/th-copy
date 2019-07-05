<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

function get_answer( int $num ) {
  switch ( $num ) {
    case 1:
      return "I love it.";
      break;
    case 2:
      return "Not my favorite."; 
      break;
    case 3:
      return "I never read it.";
      break;
  }
}


$user_id = $_POST['user'];
$poll_result = json_decode( $_POST['poll_info'] );

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');

// first put entry into hachayol poll
$qry = "insert into hachayol_poll 
        set user_id = " . $user_id . ", 
        notes = '" . $poll_result->notes . "'";
$result = mysql_query( $qry );
if ( $result ) $poll_id = mysql_insert_id();
else $success = false;

// add answers to hachayol poll details
if ( $poll_id ) {
  foreach ( $poll_result as $question => $answer ) {
    if ( $question != "notes" ) {
      $qry = "insert into hachayol_poll_details  
              set hachayol_poll_id = " . $poll_id . ",  
              question = \"" . $question . "\", 
              answer = '" . get_answer( $answer ) . "'";
      if ( !mysql_query( $qry ) ) {
        $success = false;
        break;
      }
    } 
  }
}

if ( $success ) {
  mysql_query('commit');
  echo "Thank you for taking our poll.";
} else {
  mysql_query('rollback');
  echo "There was an error entering your poll.";
}
mysql_query('set autocommit=1'); 
?>