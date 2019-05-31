<?php 
require_once '../../db.php';

$qrys = [];
$sql = "
  SELECT 
      d.donor_id 
  FROM
      mashpia_charidy.donors d
          JOIN
      mashpia_charidy.donations dd USING (donor_id)
  WHERE
      donor_id NOT IN (SELECT 
              donor_id
          FROM
              charidy_donors_callers
          WHERE
              year = 5779)
          AND parent_admin_id IN (SELECT 
              admin_id
          FROM
              admin_auths aa
                  JOIN
              mashpia_charidy.donors d ON d.parent_admin_id = aa.admin_id
                  JOIN
              th_chidon tc ON tc.user_id = aa.id
          WHERE
              tc.year = 5779 AND tc.date_paid > 0)
  GROUP BY donor_id
";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $donor_id = $row['donor_id'];
  $qrys[] = "insert into charidy_donors_callers 
            set year = 5779, 
            charidy_caller_id = 22, 
            donor_id = " . $donor_id;
}

$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');
foreach ( $qrys as $qry ) {
  if ( !mysql_query( $qry ) ) {
    echo "Error = " . mysql_error() . "<br />" . $qry . "<br />";
    $success = false;
    break;
  }
}
if ( $success ) {
  echo "Done.";
  mysql_query('commit');
} else {
  mysql_query('rollback');
}
mysql_query('set autocommit=1');