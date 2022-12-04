<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$chidon_year = $year;
if ( isset( $_POST['year'] ) ) $year = intval( $_POST['year'] );
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
    <!-- <meta http-equiv="refresh" content="5"/> -->
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
    <form method="post">
      <p>
        <select name="year">
          <?php
          for ( $i = 5779; $i <= $chidon_year; $i++ ) {
            echo "<option value='" . $i . "'";
            if ( $year == $i ) echo "selected='selected' ";
            echo ">" . $i . "</option>";
          }
          ?>
        </select>
        <input type="submit" name="submit" value="Change" />
      </p>
    </form>
    Totals:
    <?php if ($year < 5783) : ?>
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
    </table>
    <br /><br />
    Details:
    <table>
      <tr>
        <th>Parent ID</th>
        <th>Name</th>
        <th>Total Raised</th>
      </tr>
      <?php
      $stmt = $MASHPIA_DB->prepare("
        SELECT 
            for_family_id, SUM(donation_amount) as total, a.*
        FROM
            mashpiadb.chidon_donations d
                LEFT JOIN
            admins a ON a.admin_id = d.for_family_id
        WHERE
            chidon_year = :year
        GROUP BY for_family_id;
      ");
      $res = $stmt->execute([ ':year' => $year ]);
      if ( $res ) {
        $families = $stmt->fetchAll();

        $stmt = $MASHPIA_DB->prepare("
          SELECT 
              id
          FROM
              admin_auths
          WHERE
              admin_id = :id AND role_id = 1
        ");

        $stmt2 = $MASHPIA_DB->prepare("
          SELECT 
              u.first, IFNULL( SUM(subsidy_amount), 0 ) AS total, tc.rohr_subsidy, tc.paid 
          FROM
              chidon_user_subsidies 
                JOIN 
              users u using (user_id) 
                JOIN
              th_chidon tc using (user_id) 
          WHERE
              user_id = :id AND chidon_year = :year
                AND tc.year = :year
        ");        

        foreach ( $families as $family ) {
          echo "<tr><td>" . $family['admin_id'] . "</td><td>" . $family['first'] . ' ' . $family['last'] . "</td><th>" . $family['total'] . "</th></tr>";
          if ( $family['for_family_id']  > 0 ) {
            $res = $stmt->execute([ ':id' => $family['for_family_id'] ]);
            if ( $res ) {
              $children = $stmt->fetchAll();
              if ( !empty( $children ) ) {
                echo "<tr><td></td><td colspan='2'><table><tr><th>Child</th><th>Applied Subsidy</th><th>Rohr</th><th>Reg Paid</th></tr>";
                $sum = 0;
                foreach ( $children as $child ) {
                  $res2 = $stmt2->execute([
                    ':id'   =>  $child['id'], 
                    ':year' =>  $year
                  ]);
                  if ( $res2 ) {
                    $childInfo = $stmt2->fetch();
                    if ( $childInfo['total'] > 0 ) {
                      $sum += $childInfo['total'] + $childInfo['paid'] + ( $childInfo['rohr_subsidy'] ? 100 : 0 );
                      echo "<tr><td>" . $childInfo['first'] . "</td><td>" . $childInfo['total'] . "</td><td>" . ( $childInfo['rohr_subsidy'] ? 100 : 0 ) . 
                        "</td><td>" . $childInfo['paid'] . "</td></tr>";
                    }
                  }
                }
                echo "<tr><th align='right'>Grand Total:</th><th>" . $sum . "</th></tr></table></td></tr>";
              }
            } 
          }
        }
      }
      ?>
    </table>
    <?php else: ?>
      <table>
      <tr>
        <th>Donations</th>
        <th>Shabbaton Fee</th>
        <th>Grand Total</th>
        <th>Children Enrolled</th>
      </tr>
      <tr>
        <td><?=number_format($totals['donation'],2)?></td>
        <td><?=number_format($totals['reg'],2)?></td>
        <td>
        <?php
        $total = 0;
        foreach ( $totals as $k => $num ) {
          // if ( $k == 'rohr' ) $total += floatval( $num * 100 );
          if ( $k == 'rohr' ) continue;
          else $total += floatval( $num );
        }
        echo number_format( $total, 2 );
        ?>
        </td>
        <td><?=$children?></td>
      </tr>
    </table>
    <br /><br />
    Details:
    <table>
      <tr>
        <th>Parent ID</th>
        <th>Name</th>
        <th>Total Raised</th>
      </tr>
      <?php
      $stmt = $MASHPIA_DB->prepare("
        SELECT 
            for_family_id, SUM(donation_amount) as total, a.*
        FROM
            mashpiadb.chidon_donations d
                LEFT JOIN
            admins a ON a.admin_id = d.for_family_id
        WHERE
            chidon_year = :year
        GROUP BY for_family_id;
      ");
      $res = $stmt->execute([ ':year' => $year ]);
      if ( $res ) {
        $families = $stmt->fetchAll();

        $stmt = $MASHPIA_DB->prepare("
          SELECT 
              id
          FROM
              admin_auths
          WHERE
              admin_id = :id AND role_id = 1
        ");

        $stmt2 = $MASHPIA_DB->prepare("
          SELECT 
              u.first, IFNULL( SUM(subsidy_amount), 0 ) AS total, tc.rohr_subsidy, tc.paid 
          FROM
              chidon_user_subsidies 
                JOIN 
              users u using (user_id) 
                JOIN
              th_chidon tc using (user_id) 
          WHERE
              user_id = :id AND chidon_year = :year
                AND tc.year = :year
        ");        

        foreach ( $families as $family ) {
          echo "<tr><td>" . $family['admin_id'] . "</td><td>" . $family['first'] . ' ' . $family['last'] . "</td><th>" . $family['total'] . "</th></tr>";
          if ( $family['for_family_id']  > 0 ) {
            $res = $stmt->execute([ ':id' => $family['for_family_id'] ]);
            if ( $res ) {
              $children = $stmt->fetchAll();
              if ( !empty( $children ) ) {
                echo "<tr><td></td><td colspan='2'><table><tr><th>Child</th><th>Applied Subsidy</th><th>Reg Paid</th></tr>";
                $sum = 0;
                foreach ( $children as $child ) {
                  $res2 = $stmt2->execute([
                    ':id'   =>  $child['id'], 
                    ':year' =>  $year
                  ]);
                  if ( $res2 ) {
                    $childInfo = $stmt2->fetch();
                    if ( $childInfo['total'] > 0 ) {
                      $sum += $childInfo['total'] + $childInfo['paid'];
                      echo "<tr><td>" . $childInfo['first'] . "</td><td>" . $childInfo['total'] . "</td><td>" . $childInfo['paid'] . "</td></tr>";
                    }
                  }
                }
                echo "<tr><th align='right'>Grand Total:</th><th>" . $sum . "</th></tr></table></td></tr>";
              }
            } 
          }
        }
      }
      ?>
    </table>
    <?php endif; ?>
  </body>
</html>