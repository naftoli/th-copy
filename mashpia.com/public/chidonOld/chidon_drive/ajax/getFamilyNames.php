<?php
ini_set('display_errors',1);
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$schools = [];
$community = $_POST['community'];
if ( !empty( $community ) ) {
  // get schools 
  require __DIR__ . '/../classes/Communities.php';
  $c = new Communities;
  $communities = $c->getCommunities();
  $schools = $communities[ urldecode($community) ];
}

$names = [];
$qry = "
  SELECT 
      a.admin_id, a.last, a.admin_city, a.admin_postal
  FROM
      admins a
          JOIN
      th_chidon tc on tc.parent_id = a.admin_id 
  WHERE
      a.last != '' AND tc.year = :year
        AND tc.fundraising_goal > 0
";
if ( !empty( $schools ) ) $qry .= "
  AND th.user_id IN (SELECT 
          user_id
      FROM
          users
      WHERE
          school_id IN (" . implode(',', $schools) . "))
";
$qry .= "GROUP BY a.admin_id";
$stmt = $MASHPIA_DB->prepare( $qry );
$res = $stmt->execute([
  ':year' =>  $year
]);
if ( $res ) {
  $names = $stmt->fetchAll();
}

echo json_encode( $names );
?>