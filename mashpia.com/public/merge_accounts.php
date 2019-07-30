<?php
ini_set('display_errors',1);
$admin_auth = array('school');
require 'header.php';

if ( $admin_user['auth'] != 'super' ) {
  echo "You are not authorized to view this page.";
  exit;
}

if ( isset( $_POST['submit'] ) ) {
	$from = $_POST['from'];
	$to = $_POST['to'];
	$msg = "";
	
	if ( $from != "" && $to != "" ) {
		$sql = "select user_id from users where user_serial = " . $from;
		$result = mysql_query( $sql );
		if ( mysql_num_rows($result) > 0 ) {
			$row = mysql_fetch_assoc($result);
			$from = $row['user_id'];
		} else {
			$msg .= "You have entered an incorrect serial number in the old account textbox.<br />";
		}
		
		$sql = "select user_id from users where user_serial = " . $to;
		$result = mysql_query( $sql );
		if ( mysql_num_rows($result) > 0 ) {
			$row = mysql_fetch_assoc($result);
			$to = $row['user_id'];
		} else {
			$msg .= "You have entered an incorrect serial number in the new account textbox.<br />";
		}
		
		if ( empty( $msg ) ) {
      // keep track of original medals / ranks
      $medals = [];
      $sql = "select * from medal_marks where user_id = " . $from;
      $result = mysql_query( $sql );
      while ( $row = mysql_fetch_assoc( $result ) ) {
        $medals[] = $row;
      }

      $ranks = [];
      $sql = "select * from rank_marks where user_id = " . $from;
      $result = mysql_query( $sql );
      while ( $row = mysql_fetch_assoc( $result ) ) {
        $ranks[] = $row;
      }

      mysql_query('set autocommit=0');
      mysql_query('begin');

      $sqlTasks = "update ignore date_tasks_marks set user_id = $to where user_id = $from";
      $sqlMissions = "update ignore date_tasks_mission_marks set user_id = $to where user_id = $from";

      if ( mysql_query( $sqlTasks ) && mysql_query( $sqlMissions ) ) {
        $updated = true;
        mysql_query('commit');
      } else {
        echo $sqlTasks . "<br />" . $sqlMissions . "<br />" . mysql_error();
        $updated = false;
        mysql_query('rollback');
      }
      mysql_query('set autocommit=1');

			if ( $updated ) {
				//require_once('classes/mission_marks_updater.php');
				require_once('classes/medal_updater.php');
				require_once('classes/rank_updater.php');
				
				//$mmupdater = new mission_marks_updater();
				$mupdater = new medal_updater();
				$rupdater = new rank_updater();
				
				$user = $to;
				//$mmupdater->mission_marks_update( $user );
				$mupdater->update_medal_two( $user );
        $rupdater->update_rank_two( $user );
        
        // update any medals / ranks earned and received info from old to new
        foreach ( $medals as $medal ) {
          $subject = $medal['subject_id'];
          $medal_ord = $medal['medal_ord'];
          $sql = "select * from medal_marks where subject_id = " . $subject . " and medal_ord = " . $medal_ord . " and user_id = " . $to;
          $result = mysql_query( $sql );
          if ( mysql_num_rows( $result ) > 0 ) {
            // if medal was awared now by updater, update to have info from old account
            $row = mysql_fetch_assoc( $result );
            if ( $row['date_awarded'] == unixtojd() ) {
              $sql = "update medal_marks 
                      set date_awarded = " . $medal['date_awarded'];
              if ( $medal['date_shipped'] ) $sql .= ", date_shipped = '" . $medal['date_shipped'] . "'";
              if ( $medal['date_received'] ) $sql .= ", date_received = '" . $medal['date_received'] . "'";
              $sql .= " 
                      where medal_ord = " . $medal_ord . ", 
                      and subject_id = " . $subject . ", 
                      and user_id = " . $to;
              mysql_query( $sql );
            }
          }
        }

        foreach ( $ranks as $rank ) {
          $sql = "select * from rank_marks where rank_ord = " . $rank['rank_ord'] . " and user_id = " . $to;
          $result = mysql_query( $sql );
          if ( mysql_num_rows( $result ) > 0 ) {
            // if rank was awared now by updater, update to have info from old account
            $row = mysql_fetch_assoc( $result );
            if ( $row['date_promoted'] == unixtojd() ) {
              $sql = "update rank_marks 
                      set data_promoted = " . $rank['data_promoted'];
              if ( $rank['date_printed'] ) $sql .= ", date_printed = '" . $rank['date_printed'] . "'";
              if ( $rank['date_book_shipped'] ) $sql .= ", date_book_shipped = '" . $rank['date_book_shipped'] . "'";
              if ( $rank['date_book_received'] ) $sql .= ", date_book_received = '" . $rank['date_book_received'] . "'";
              if ( $rank['date_card_shipped'] ) $sql .= ", date_card_shipped = '" . $rank['date_card_shipped'] . "'";
              if ( $rank['date_card_received'] ) $sql .= ", date_card_received = '" . $rank['date_card_received'] . "'";
              $sql .= " where user_id = " . $to;
              mysql_query( $sql );
            }
          }
        }

        // delete old medals / ranks
        $sql = "delete from medal_marks where user_id = " . $from;
        $sql2 = "delete from rank_marks where rank_ord != 1 and user_id = " . $from;
        mysql_query( $sql );
        mysql_query( $sql2 );
				
				$msg .= "The two accounts have been merged<br />";
			} else {
				$msg .= "Error trying to merge the two accounts.<br />" . $sql . "<br />" . mysql_error() . "<br />";
			}
		}
	} else {
		$msg .= "You have not entered a correct value for the old and / or new account.<br />Please try again!<br />";
	}
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</head>

	<body>
		<? require 'admin_header.php'; ?>
		<h1>Merge Two Accounts</h1>
		
		<div style="color: red">
			<? 
			if ( isset( $msg ) ) {
				echo $msg . "<br />";
			}
			?>
		</div>
		
		<form action="merge_accounts.php" method="post">
			Old account serial number: <input type="text" name="from" /><br />
			New account serial number: <input type="text" name="to" /><br />
			<input type="submit" name="submit" value="Merge" />
		</form>
	</body>
</html>
