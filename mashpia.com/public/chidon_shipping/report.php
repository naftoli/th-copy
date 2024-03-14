<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonRegYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.chidonShipping.php';
require 'data.php';

//$updated = getUpdatedSchools($schools);

$items_chosen = isset($_POST['items']) ? $_POST['items'] : [];
$fields_chosen = array_keys($_POST['fields']);
$item_details_chosen = isset($_POST['details']) ? array_keys($_POST['details']) : [];
$limit_to_status = isset($_POST['status']) ? $_POST['status'] : [];
$ship_to = isset($_POST['ship_to']) ? $_POST['ship_to'] : 'all';
$list_of_schools = $_POST['school'];

$cs = new ChidonShipping();
$cs->setYear($year);

$report_type = $_POST['report_type'];
if ($report_type == 'file') {
    $files = [];
    $status = $cs->getStatus();
//    echo "<pre>"; print_r($status); echo "</pre>";
    foreach ($list_of_schools as $school_id) {
        foreach ($items_chosen as $cat => $itemsPerCat) {
            $listOfItems = array_keys($itemsPerCat);
            $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
            $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems);
        }
        // remove shipped items if needed
        foreach ($limit_to_status as $status_num) {
            foreach ($info as $cat => $details) {
                foreach ($details as $user => $items) {
                    $item_num = 0;
                    foreach ($items as $idx => $item) {
                        // find out how many of the same item we have
                        if ($idx > 0 && $item['id'] == $items[$idx - 1]['id']) $item_num++;
                        switch ($status_num) {
                            case 0:
                                if (isset($status[$user][$item['id']]) && $status[$user][$item['id']][$item_num]['shipped'] == 1) unset($info[$cat][$user][$idx]);
                                break;
                            case 1:
                                if (!isset($status[$user][$item['id']]) || $status[$user][$item['id']][$item_num]['shipped'] == 0) unset($info[$cat][$user][$idx]);
                                break;
                            case 2:
                                if (!isset($status[$user][$item['id']]) || $status[$user][$item['id']][$item_num]['missing'] == 0) unset($info[$cat][$user][$idx]);
                                break;
                            case 3:
                                if (!isset($status[$user][$item['id']]) || $status[$user][$item['id']][$item_num]['received'] == 0) unset($info[$cat][$user][$idx]);
                                break;
                        }
                    }
                }
            }
        }

        if ($ship_to == 'domestic') {
            $csv = createCSV($info, $year, $school_id, true); // filter out all users that ONLY live in the usa
            $file = $school_id . '-usa.csv';
        } else if ($ship_to == 'intl') {
            $csv = createCSV($info, $year, $school_id, false, true); // filter out all users that do NOT live in the usa
            $file = $school_id . '-intl.csv';
        } else {
            $csv = createCSV($info, $year, $school_id);
            $file = $school_id . '.csv';
        }
        createFile($file, $csv);
        $files[] = $file;
    }
    createZip($files, 'shipping.zip');
    downloadFile('shipping.zip');
    exit;
}

// get list of schools to iterate over
$list_of_schools = $_POST['school'];

// get results for chosen items
$info = [];
foreach ($list_of_schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        if (!isset($info[$cat])) $info[$cat] = [];
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        $info[$cat] += $cs->$nameOfFunc($_POST['gender'], $schoolID, $listOfItems);
//        if ($cat == 'gear') $info[$cat] += $cs->$nameOfFunc($_POST['gender'], $schoolID, $listOfItems);
//        else $info[$cat] += $cs->$nameOfFunc($_POST['gender'], $schoolID, $listOfItems);
    }
}

if ($ship_to != 'all') {
    // get users based on shipping destination
    if ($ship_to == 'domestic') {
        $stmt = $MASHPIA_DB->query("
            SELECT aa.id FROM admin_auths aa 
            JOIN users u on u.user_id = aa.id 
            JOIN admins a on a.admin_id = aa.admin_id 
            WHERE u.user_registered > 0 
            AND a.admin_country IN ('USA', 'US', 'United States', 'U.S.A', 'Unites States of America')
        ");
    } else if ($ship_to == 'intl') {
        $stmt = $MASHPIA_DB->query("
            SELECT aa.id FROM admin_auths aa 
            JOIN users u on u.user_id = aa.id 
            JOIN admins a on a.admin_id = aa.admin_id 
            WHERE u.user_registered > 0 
            AND a.admin_country NOT IN ('USA', 'US', 'United States', 'U.S.A', 'Unites States of America')
        ");
    }

    $rows = $stmt->fetchAll();
    $user_ids = [];
    foreach ($rows as $row) {
        $user_ids[] = $row['id'];
    }

    // remove all users that are not in the user_ids array
    foreach ($info as $cat) {
        foreach ($cat as $user => $items) {
            if (! in_array($user, $user_ids)) unset($info[$cat][$user]);
        }
    }
}

$info['status'] = $cs->getStatus();

// find all unique tables to fetch from
$tables = [];
foreach ($fields_chosen as $field) {
    $pos = strpos($field, '.');
    $table = substr($field, 0, $pos);
    if (!in_array($table, $tables)) $tables[] = $table;
}

// build sql statement
$tableAliases = [
    'tc' => 'join th_chidon tc using (user_id)',
    's' => 'join schools s on u.school_id = s.school_id',
    'c' => 'join classes c on c.class_id = u.class_id',
    'cup' => 'join chidon_user_prizes cup using (user_id) ',
    'cp' => 'join chidon_prizes cp using (chidon_prize_id) '
];

//********* SELECT **********//
$sql = "SELECT u.user_id, u.school_id ";
foreach ($fields_chosen as $field) $sql .= ", " . $field;
$sql .= " FROM users u ";
foreach ($tables as $table) {
    if ($table == 'u') continue;
    $sql .= $tableAliases[$table] . " ";
}

//********* WHERE *********//
$sql .= " WHERE 1";
$sql .= " AND u.school_id in (" . implode(",", $list_of_schools) . ")";
if (in_array('tc', $tables)) $sql .= " AND tc.year = " . $year;
//if ($_POST['school'] > 0) $sql .= " AND u.school_id = " . $_POST['school'];
if ($_POST['gender'] == 'm') $sql .= " AND u.gender = 'M'";
else if ($_POST['gender'] == 'f') $sql .= " AND u.gender = 'F'";

//******* ORDER BY *********//
$sql .= " ORDER BY u.school_id";
if (in_array('c.class_grade', $fields_chosen)) $sql .= ", c.class_grade";
if (in_array('c.class_sub', $fields_chosen)) $sql .= ", c.class_sub";
if (in_array('u.last', $fields_chosen)) $sql .= ", u.last";
if (in_array('u.first', $fields_chosen)) $sql .= ", u.first";

$stmt = $MASHPIA_DB->query($sql);
$results = $stmt->fetchAll();

$resultsBySchool = [];
foreach ($results as $row) {
    $resultsBySchool[$row['school_id']][] = $row;
}

$summary = []; // for schools
$grand_summary = []; // for HQ
$summary_items = []; // mapping of item ID to item info

// go through it once so that we can have totals
foreach ($resultsBySchool as $school => $more) {
    if (!isset($schools[$school])) continue;
    foreach ($more as $row) {
//        if (isset($row['class_grade']) && !in_array($row['class_grade'], ['3', '4', '5', '6', '7', '8', '9'])) continue;
        createHtmlForItem($school, $row, false);
    }
}

// sort summary
foreach ($summary as $school => $more) ksort($summary[$school]);
ksort($grand_summary);

//echo "<pre>"; print_r($info); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shipping Reports</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
  <style>
    body {
      font-family: sans-serif;
      font-size: 14px;
      padding-left: 3%;
      padding-right: 3%;
    }

    th, th, td {
      font-size: 14px;
      padding: 5px;
      border-bottom: 1px solid grey;
    }

    .header {
      font-size: 14px;
      line-height: 1.4;
      margin-bottom: 20px;
    }

    .dataTables_filter {
      display: none;
    }

    @media print {
      .no-print {
        display: none;
      }
    }

    button {
      padding: 8px;
      font-size: 14px;
    }

    button#saveAll {
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>
<body>
<?php foreach ($resultsBySchool as $school => $more) : ?>
  <div class="header" id="<?= $school ?>">
      <?php

      if (!isset($schools[$school])) continue;
      if (!isset($summary[$school])) continue;
      if ($super) echo "<button class='saveAll no-print'>Save All Schools as Shipped</button><br /><br />";
      echo "<h3>" . $schools[$school] . "</h3>";
      echo "<button class='saveSchool no-print'>Save " . $schools[$school] . " as Shipped</button>";
      $address = '';
      foreach ($fields_chosen as $field) {
          $pos = strpos($field, '.');
          $desc = substr($field, $pos + 1);
          switch ($field) {
              case 's.shipping_first':
              case 's.shipping_last':
                  $address .= $more[0][$desc] . ' ';
                  break;
              case 's.shipping_phone':
                  $address = "Contact Phone Number: " . $more[0][$desc] . "<br />";
                  break;
              case 's.shipping_address1':
              case 's.shipping_address2':
              case 's.shipping_city':
              case 's.shipping_state':
              case 's.shipping_postal':
              case 's.shipping_country':
                  $address .= $more[0][$desc] . ' ';
                  break;
              case 's.shipping_requests':
                  $address .= "<br />Shipping Requests: " . $more[0][$desc];
                  break;
          }
      }
      if (!empty($address)) echo "<br />" . $address . "<br />";
      ?>
    <!--      <p>-->
    <!--        <input type='checkbox' class='updated' value='--><?php //= $updated[$school] ?><!--'-->
    <!--        --><?php //if (intval($updated[$school]) == 1) echo "checked"; ?>
    <!--        /> I have reviewed and updated the shipping status for the entire school.-->
    <!--      </p>-->
      <?php if (in_array($_POST['report_type'], ['all', 'summary'])) : ?>
        <h3>Summary</h3>
        <table class="table table-striped table-condensed cell-border hover row-order order-column">
          <thead>
          <tr>
            <th>Item ID</th>
            <th>Quantity</th>
            <th>Item Name</th>
            <th>Size</th>
            <th>Gender/Color</th>
            <th>Category</th>
            <!--            <th>Status</th>-->
          </tr>
          </thead>
          <tbody>
          <?php
          if (isset($summary[$school])) {
              foreach ($summary[$school] as $id => $qty) {
                  echo "<tr><td>" . $id . "</td><td>" . $qty . "</td>";
                  $item = $summary_items[$id];
                  foreach (['item', 'size', 'color', 'cat'] as $attr) {
                      echo "<td>";
                      if (isset($item[$attr])) echo $item[$attr];
                      echo "</td>";
                  }
                  echo "</tr>";
              }
          }
          ?>
          </tbody>
        </table>
        <p class="no-print"></p>
        <div style="page-break-after: always"></div>
      <?php endif; ?>
      <?php if (in_array($_POST['report_type'], ['all', 'details'])) : ?>
          <?= "<h3>" . $schools[$school] . ' - ' . $year . "</h3>"; ?>
        <table class="table table-striped table-condensed cell-border hover row-order order-column">
          <thead>
          <tr>
              <?php
              foreach ($fields_chosen as $field) {
                  if (strpos($field, 'shipping') === false) echo "<th>" . $fields[$field] . "</th>";
              }
              echo "<th>Item</th>";
              if ($item_details_chosen && count($item_details_chosen)) {
                  foreach ($item_details_chosen as $field) {
                      if ($field == 'cat') $field = 'category';
                      else if ($field == 'name') $field = 'name preference';
                      else if ($field == 'id') $field = 'Item ID';
                      echo "<th>" . ucwords($field) . "</th>";
                  }
              }
              echo "<th class='no-print'>Status</th>";
              echo "<th class='no-print'>Explain the damage</th>"
              ?>
          </tr>
          </thead>
          <tbody>
          <?php
          foreach ($more as $row) {
//            if (isset($row['class_grade']) && !in_array($row['class_grade'], ['3', '4', '5', '6', '7', '8', '9'])) continue;
              createHtmlForItem($school, $row);
          }
          ?>
          </tbody>
        </table>
        <p class="no-print"></p>
        <div style="page-break-after: always"></div>
      <?php endif; ?>
  </div>
<?php endforeach; ?>
<?php
if ($admin_user['auth'] == 'super' && isset($_POST['grand_summary']) && $_POST['grand_summary'] == 1) {
    foreach ($grand_summary as $item => $more) {
        $grand_total = 0;
        $item_details = $summary_items[$item];
        $desc = "($item)";
        foreach (['item', 'size', 'color'] as $attr) {
            if (isset($item_details[$attr])) $desc .= ' ' . $item_details[$attr];
        }
        echo "<br />";
        echo "<h2>" . ucwords($desc) . " Totals</h2>";
        ?>
      <table class="table table-striped table-condensed cell-border hover row-order order-column grandTotal">
        <thead>
        <tr>
          <th>School</th>
          <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($more as $school_id => $total) {
            echo "<tr><td>" . $schools[$school_id] . "</td><td>" . $total . "</td></tr>";
            $grand_total += intval($total);
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
          <th>Grand Total:</th>
          <th><?= $grand_total; ?></th>
        </tr>
        </tfoot>
      </table>
      <div style="page-break-after: always;"></div>
        <?php
    }
}
?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
  $('.table').DataTable({
    paging: false
  });

  let info = []
  const super_admin = <?= $super ? 1 : 0; ?>;

  function update(elem, action, desc = '') {
    const id = $(elem).attr('id')
    const ids = id.split(':')
    const item = ids[0]
    const user = ids[1]
    const num = ids[2]
    // get description
    info.push({action, item, user, desc, num})
  }

  function save(reload = true) {
    $.post('ajax/saveShipping.php', {info}, function (result) {
      const res = JSON.parse(result)
      if (res.success) {
        if (reload) location.reload()
      } else alert(res.error)
    })
  }

  $(".saveAll").click(function () {
    $(".shipping").each(function () {
      update(this, 1)
    })
    save()
  })

  $(".saveSchool").click(function () {
    $(this).parent().find('.shipping').each(function () {
      update(this, 1)
    })
    save()
  })

  $(".shipping").change(function () {
    const action = parseInt(this.value)
    if (!super_admin && action == 0) {
      alert('You cannot change this to not yet shipped, it will not be saved.')
      return false
    } else if (!super_admin && action == 3) {
      alert('You must explain the damage before it can be saved.')
      return false
    }
    update(this, action)
    save(false)
  })

  $(".description").blur(function () {
    const val = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    update(elem, action, val)
    save(false)
  })

  // $(".updated").click( function() {
  //   const school_id = $(this).parent().parent().attr('id')
  //   const checked = $(this).is(":checked") ? 1 : 0
  //   $.post('ajax/updateSchool.php', { school_id, checked }, function (result) {
  //     const res = JSON.parse(result)
  //     if (!res.success) alert(res.error)
  //   })
  // })

  <!--  --><?php //if (!$super) : ?>
  // $("select").attr('disabled', true)
  // $("textarea").attr('disabled', true)
  <!--  --><?php //endif; ?>
</script>
</html>