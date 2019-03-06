<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$totals = []; 

$stmt = $MASHPIA_DB->prepare("
  SELECT 
      SUM(donation_amount) as donation 
  FROM
      chidon_donations 
  WHERE
      chidon_year = :year
");
$res = $stmt->execute([':year' => $year]);
if ( $res ) {
  $row = $stmt->fetch();
  $totals['donation'] = $row['donation'];
}

$stmt = $MASHPIA_DB->prepare("
  SELECT 
      SUM(rohr_subsidy) as rohr, sum(paid) as reg
  FROM
      th_chidon
  WHERE
      year = :year
");
$res = $stmt->execute([':year' => $year]);
if ( $res ) {
  $row = $stmt->fetch();
  $totals['rohr'] = $row['rohr'];
  $totals['reg'] = $row['reg'];
}

$children = 0;
$stmt = $MASHPIA_DB->prepare("
  SELECT 
      COUNT(*) AS total 
  FROM
      th_chidon
  WHERE
      year = :year AND date_paid > 0
");
$res = $stmt->execute([':year' => $year]);
if ( $res ) {
  $row = $stmt->fetch();
  $children = $row['total'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <title>Chidon Drive Reporting</title>
    <style>
      tr, th, td {
        font-family: Arial;
        font-size: 14px;
        padding: 5px;
      }
    </style>
  </head>
  <body>
    Totals:
    <table>
      <tr>
        <th>Donations</th>
        <th>Shabbaton Fee</th>
        <th>Rohr Subsidy</th>
        <th>Grand Total</th>
        <th>Children Enrolled</th>
      </tr>
      <tr>
        <td><?=number_format($totals['donation'],2)?></td>
        <td><?=number_format($totals['reg'],2)?></td>
        <td><?=number_format($totals['rohr'] * 100, 2)?></td>
        <td>
        <?php
        $total = 0;
        foreach ( $totals as $k => $num ) {
          if ( $k == 'rohr' ) $total += floatval( $num * 100 );
          else $total += floatval( $num );
        }
        echo number_format( $total, 2 );
        ?>
        </td>
        <td><?=$children?></td>
      </tr>
  </body>
</html>