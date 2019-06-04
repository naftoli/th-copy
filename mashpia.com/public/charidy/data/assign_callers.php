<?php
ini_set('display_errors',1);
require_once '../../db.php';

$callers = [29,30];
$sql = "
SELECT 
    donor_id
FROM
    mashpia_charidy.donors
WHERE
    phone NOT IN ('' , '..', '...', '....', '.....')
        AND (parent_admin_id is null or parent_admin_id not IN (SELECT 
            admin_id
        FROM
            admin_auths aa
                JOIN
            th_chidon tc ON tc.parent_id = aa.admin_id
        WHERE
            tc.date_paid > 0))
        AND donor_id NOT IN (SELECT 
            donor_id
        FROM
            charidy_donors_callers
        WHERE
            year = 5779)
        AND donor_id NOT IN (SELECT 
            donor_id
        FROM
            mashpia_charidy.donations
        GROUP BY donor_id)
GROUP BY donor_id
LIMIT 1853
";
foreach ( $callers as $caller_id ) {
  $result = mysql_query( $sql );
  while ( $row = mysql_fetch_assoc( $result ) ) {
    $qry = "insert into charidy_donors_callers 
            set donor_id = " . $row['donor_id'] . ", 
            charidy_caller_id = " . $caller_id . ", 
            year = 5779";
    mysql_query( $qry ) or die( mysql_error() );
  }
}
echo "done.";
?>