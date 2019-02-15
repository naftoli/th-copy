<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$names = [];
$stmt = $MASHPIA_DB->prepare("
  SELECT 
      a.admin_id, a.last, a.admin_city, a.admin_postal
  FROM
      admins a
          JOIN
      th_chidon tc ON tc.parent_id = a.admin_id
  WHERE
      tc.year = :year AND a.last != '' 
          AND (tc.contestant = 1 or tc.school_rep = 1) 
  GROUP BY a.admin_id
  ORDER BY a.last
");
$res = $stmt->execute([
  ':year' =>  $year
]);
if ( $res ) {
  $names = $stmt->fetchAll();
}

echo json_encode( $names );
?>