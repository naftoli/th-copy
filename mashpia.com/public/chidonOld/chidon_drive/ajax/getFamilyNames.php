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
      chidon_user_goals cug USING (admin_id)
  WHERE
      a.last != '' AND cug.year = :year
";
if ( !empty( $schools ) ) $qry .= "
  AND cug.user_id IN (SELECT 
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