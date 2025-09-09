<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

// info on all schools
$sqlSchools = "select school_id, school_name from schools";
$stmtSchools = $MASHPIA_DB->query($sqlSchools);
$rows = $stmtSchools->fetchAll();
foreach ($rows as $row) {
    $all_schools[$row['school_id']] = $row['school_name'];
}

// first get all admins for all children in each school
$sqlAdmins = "select a.* from admins a 
                join admin_auths aa using (admin_id) 
                join users u on u.user_id = aa.id 
                join user_registration ur on ur.user_id = u.user_id 
                where u.user_registered > 0 
                and u.school_id = :school 
                and ur.year = :year 
                group by admin_id 
                order by a.last, a.first";
$stmtAdmins = $MASHPIA_DB->prepare($sqlAdmins);

if ($year < 5786) {
    // then get all users per admin
    $sqlUsers = "select u.user_id, u.school_id, hachayol, first, c.class_grade, c.class_sub, ur.reg_date from users u 
                join classes c on c.class_id = u.class_id 
                join admin_auths aa on u.user_id = aa.id 
                left join user_registration ur on ur.user_id = u.user_id 
                where u.user_registered > 0 and aa.admin_id = :id 
                and ur.year = :year 
                order by u.dob";
    $stmtUsers = $MASHPIA_DB->prepare($sqlUsers);

    // get users that don't have an admin account
    $sqlMissing = "select u.user_id, u.school_id, hachayol, first, last, c.class_grade, c.class_sub, ur.reg_date from users u 
                    join classes c on c.class_id = u.class_id 
                    left join admin_auths aa on aa.id = u.user_id 
                    left join user_registration ur on ur.user_id = u.user_id 
                    where u.user_registered > 0 
                    and aa.admin_id is null 
                    and u.school_id = :school 
                    and ur.year = :year";
    $stmtMissing = $MASHPIA_DB->prepare($sqlMissing);
} else {
    // then get all users per admin
    $sqlUsers = "select u.user_id, u.school_id, first, c.class_grade, c.class_sub, ur.reg_date, u.hachayol as hachayol_status,  
                  IF(htg.user_id IS NOT NULL, 1, 0) as hachayol
                from users u 
                join classes c on c.class_id = u.class_id 
                join admin_auths aa on u.user_id = aa.id 
                left join user_registration ur on ur.user_id = u.user_id 
                left join hachayols_to_give htg on htg.user_id = u.user_id and htg.year = ur.year 
                where u.user_registered > 0 and aa.admin_id = :id 
                and ur.year = :year 
                order by u.dob";
    $stmtUsers = $MASHPIA_DB->prepare($sqlUsers);

    // get users that don't have an admin account
    $sqlMissing = "select u.user_id, u.school_id, first, last, c.class_grade, c.class_sub, ur.reg_date, u.hachayol as hachayol_status,  
                      IF(htg.user_id IS NOT NULL, 1, 0) as hachayol
                    from users u 
                    join classes c on c.class_id = u.class_id 
                    left join admin_auths aa on aa.id = u.user_id 
                    left join user_registration ur on ur.user_id = u.user_id 
                    left join hachayols_to_give htg on htg.user_id = u.user_id and htg.year = ur.year 
                    where u.user_registered > 0 
                    and aa.admin_id is null 
                    and u.school_id = :school 
                    and ur.year = :year";
    $stmtMissing = $MASHPIA_DB->prepare($sqlMissing);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Hachayol Update Family Report</title>
  <link href="../admin_styles.css" rel="stylesheet" type="text/css">
  <script type="text/javascript" src="../scripts/jquery-1.8.3.js"></script>
  <style>

    table {
      font-size: 12px;
    }

    th {
      font-size: 14px;
    }

    tr, th, td {
      padding: 10px;
      border-bottom: 1px solid #ccc;
    }
  </style>
</head>
<body>
<?php include('../admin_header.php'); ?>
<h1>Hachayol Update Family Report</h1>
<?php $j = []; // for id in input of children ?>
<?php $unique_id = 1; ?>
<?php foreach ($schools as $school_id => $school_name) : ?>
    <?= "<h2>" . $school_name . "</h2>" ?>
  <table>
    <tr>
      <th>Family ID</th>
      <th>Family</th>
      <th>Children/Hachayol</th>
    </tr>
      <?php
      $info = [];
      $stmtAdmins->execute([
          'school' => $school_id,
          'year' => $year
      ]);
      $admins = $stmtAdmins->fetchAll();
      foreach ($admins as $admin) {
          if (isset($j[$admin['admin_id']])) $j[$admin['admin_id']]++;
          else $j[$admin['admin_id']] = 1;
          $stmtUsers->execute([
              'id' => $admin['admin_id'],
              'year' => $year
          ]);
          $children = $stmtUsers->fetchAll();
          if (!$children) continue;
//          if (!$children) $stmtUsers->debugDumpParams();
          // find out if hachayol child is in this school or not
          $disable = false;
          foreach ($children as $child) {
              if (!$super && $child['hachayol'] == 1 && $child['school_id'] != $school_id) {
                  $disable = true;
                  break;
              }
          }

          echo "<tr><td>" . $admin['admin_id'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>";
          foreach ($children as $i => $child) {
              // find out child's school / grade
              $school = $all_schools[$child['school_id']];
              $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
              $id = $admin['admin_id'] . ':' . $j[$admin['admin_id']];
              echo "<input type='radio' name='hachayol[$id]' class='hachayol' id='" . $child['user_id'] . "'";
              if ($child['hachayol'] == 1 && $child['reg_date']) echo " checked";
              if ($disable) echo " disabled";
              echo " />";
              echo $child['first'] . " (" . $school . ' : ' . $grade . ")<br />";
          }
          echo "</td></tr>";
      }
      // find kids with missing parent account
      $stmtMissing->execute([
          'school' => $school_id,
          'year' => $year
      ]);
      $missing = $stmtMissing->fetchAll();
      foreach ($missing as $idx => $child) {
          $school = $all_schools[$child['school_id']];
          $grade = $child['class_grade'] . (empty($child['class_sub']) ? '' : '-' . $child['class_sub']);
          echo "<tr><td colspan='2'>No Parent Account</td><td>";
          echo "<input type='radio' name='hachayol[" . $unique_id++ . "]' class='hachayol toCheck' id='" . $child['user_id'] . "'";
          if ($child['hachayol'] == 1 && $child['reg_date']) echo " checked";
          echo " />";
          echo $child['first'] . ' ' . $child['last'] . " (" . $school . ' : ' . $grade . ")</td></tr>";
      }
      ?>
  </table>
<?php endforeach; ?>
</body>
<script>
  // $(".toCheck").each( function() {
  //   $(this).trigger('click')
  // })
  $(".hachayol").click(function () {
    let list = []
    let elem = $(this).parent()
    // get all kids in this admin and remove from the rest
    let children = $(elem).find('input').each(function () {
      let user_id = $(this).attr('id')
      let checked = $(this).is(":checked") ? 1 : 0
      list.push({user_id, checked})
    })

    // update db
    $.post('/mobile/reg/ajax/updateHachayols.php', {list}, function (result) {
      const res = JSON.parse(result)
      if (res.success) alert('updated')
      else alert('error updating')
    })
  })
</script>
</html>