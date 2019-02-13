<?php
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
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
      tc.contestant = 1 AND tc.year = :year
          AND a.last != ''
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