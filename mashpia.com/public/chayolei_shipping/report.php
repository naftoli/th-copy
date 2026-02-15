<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getCurrentYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require 'class.chayoleiShipping.php';
require 'data.php';

//$updated = getUpdatedSchools($schools);

$items_chosen = isset($_POST['items']) ? $_POST['items'] : [];
$cats = array_keys($items_chosen);
$fields_chosen = array_keys($_POST['fields']);
$item_details_chosen = isset($_POST['details']) ? array_keys($_POST['details']) : [];
$limit_to_status = isset($_POST['status']) ? $_POST['status'] : [];
$ship_to = isset($_POST['ship_to']) ? $_POST['ship_to'] : 'all';

// get list of schools to iterate over
$list_of_schools = $_POST['school'];
$shipment_number = isset($_POST['shipment_number']) ? $_POST['shipment_number'] : 0;

// build data for printLabels.php
$for_labels = [];
$for_labels['items'] = $items_chosen;
$for_labels['medals_dates'] = $_POST['medals_dates'];
$for_labels['ranks_dates'] = $_POST['ranks_dates'];
$for_labels['gender'] = $_POST['gender'];
$for_labels['year'] = $year;
$for_labels['schools'] = $_POST['school'];
$for_labels['limit_to_status'] = $limit_to_status;
$for_labels['fields'] = $fields_chosen;
$for_labels['details'] = $item_details_chosen;
$for_labels['shipment_number'] = $shipment_number;

$cs = new ChayoleiShipping();
$cs->setYear($year);

$report_type = $_POST['report_type'];
if ($report_type == 'file') {
    $files = [];
    $status = $cs->getStatus();
    foreach ([61, 269] as $school_id) {
        foreach ($items_chosen as $cat => $itemsPerCat) {
            $listOfItems = array_keys($itemsPerCat);
            $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
            $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems);
        }
        // remove shipped items if needed
        foreach ($info as $cat => $details) {
            foreach ($details as $user => $items) {
                foreach ($items as $idx => $item) {
                  // find out how many of the same item we have
                  if ($idx > 0 && $item['id'] == $items[$idx - 1]['id']) $item_num++;
                  else $item_num = 0;
                  $item_status = isset($status[$user][$item['id']][$item_num]['status']) ? $status[$user][$item['id']][$item_num]['status'] : 0;
                  if ($limit_to_status && !in_array($item_status, $limit_to_status)) unset($info[$cat][$user][$idx]);
                }
            }
        }
        if (in_array($ship_to, ['usa', 'all'])) {
            $csv = createCSV($info, $school_id, true); // filter out all users that ONLY live in the usa
            $file = $school_id . '-usa.csv';
            createFile($file, $csv);
            $files[] = $file;
        }
        if (in_array($ship_to, ['intl', 'all'])) {
            $csv = createCSV($info, $school_id, false, true); // filter out all users that do NOT live in the usa
            $file = $school_id . '-intl.csv';
            createFile($file, $csv);
            $files[] = $file;
        }
    }
    createZip($files, 'shipping.zip');
    downloadFile('shipping.zip');
    exit;
}
/*
    $ids = $cs->getChildrenToRemove();
    foreach ([61, 269] as $school_id) {
        $info = [];
        $cs->setToExclude($ids);
        foreach ($items_chosen as $cat => $itemsPerCat) {
            $listOfItems = array_keys($itemsPerCat);
            $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
//            if ($cat == 'extra purchases') $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems, 'byFamily', $remove);
            if ($cat == 'extra purchases') continue;
            else $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems);
        }
//        echo "<pre>"; print_r($info); echo "</pre>";
        $csv = createCSV($info, $school_id);
        $file = $school_id . '.csv';
        createFile($file, $csv);
        $files[] = $file;
    }
    // add extra purchases not ak/myshliach to ship
    $extra = $cs->getExtraPurchasesToShip();
    $csv = $cs->createCSVFromExtraPurchases($extra);
    $file = 'extra_purchases.csv';
    createFile($file, $csv);
    $files[] = $file;

    /*
     * create myshliach / anash kinder with extra purchases files
     * there's 3 files needed
     * 1. for parents that paid for shipping and include extra purchases that are to be shipped to home address
     * 2. for parents that didn't pay for shipping and include extra purchases that are to be pickud up
     * 3. (for parents that paid for shipping but have) extra purchases that go to different address
     */
/*
// first
// reset the array of kids to remove
$cs->setToExclude([]);
foreach ([61, 269] as $school_id) {
    $info = [];
    $ids = $cs->getChildrenToRemove(true);
    $cs->setOnly($ids);

    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        if ($cat == 'extra purchases') $info[$cat] = $cs->getExtraPurchasesAK();
        else $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems);
    }
    $csv = createCSV($info, $school_id);
    $file = $school_id . 'withEPtoShip.csv';
    createFile($file, $csv);
    $files[] = $file;
}

//second
foreach ([61, 269] as $school_id) {
    $info = [];
    $ids = $cs->getChildrenToRemove(false, true);
    $cs->setOnly($ids);

    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        if ($cat == 'extra purchases') $info[$cat] = $cs->getExtraPurchasesAK(false);
        else $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $school_id, $listOfItems);
    }
    $csv = createCSV($info, $school_id);
    $file = $school_id . 'withEPtoPickup.csv';
    createFile($file, $csv);
    $files[] = $file;
}

//third
$extra = $cs->getExtraPurchasesToShip(true);
$csv = $cs->createCSVFromExtraPurchases($extra);
$file = 'extra_purchases_myshliach_ak.csv';
createFile($file, $csv);
$files[] = $file;

createZip($files, 'shipping.zip');
downloadFile('shipping.zip');
exit;
*/
//else if ($report_type == 'fileGear') {
//    $files = [];
//    $listOfItems = array_keys($items_chosen['gear']);
//    $info['gear'] = $cs->getGear($_POST['gender'],0, $listOfItems, true);
//    $users = array_keys($info['gear']);
//    $csv = createCSVforGear($users, $info['gear']);
//    $file = 'gear.csv';
//    createFile($file, $csv);
//    downloadFile($file);
//    exit;
//}

// get list of schools to iterate over
$list_of_schools = $_POST['school'];
$shipment_number = isset($_POST['shipment_number']) ? $_POST['shipment_number'] : 0;

// get results for chosen items
$info = [];
foreach ($list_of_schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        if (!isset($info[$cat])) $info[$cat] = [];
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        if ($cat == 'gear') $info[$cat] += $cs->$nameOfFunc($_POST['gender'], $schoolID, $listOfItems);
        else $info[$cat] += $cs->$nameOfFunc($_POST['gender'], $schoolID, $listOfItems);
    }
}
// echo "<pre>"; print_r($info); echo "</pre>"; exit;
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
if ($super && $ship_to != 'all') {
  $sql .= " join admin_auths aa on aa.id = u.user_id join admins a on a.admin_id = aa.admin_id ";
}

//********* WHERE *********//
$sql .= " WHERE 1";
$sql .= " AND u.school_id in (" . implode(",", $list_of_schools) . ")";
if (in_array('tc', $tables)) $sql .= " AND tc.year = " . $year;
//if ($_POST['school'] > 0) $sql .= " AND u.school_id = " . $_POST['school'];
if ($_POST['gender'] == 'm') $sql .= " AND u.gender = 'M'";
else if ($_POST['gender'] == 'f') $sql .= " AND u.gender = 'F'";
if ($super && $ship_to != 'all') {
    if ($ship_to == 'usa') $sql .= " AND a.admin_country = 'USA'";
    else if ($ship_to == 'intl') $sql .= " AND a.admin_country != 'USA'";
}

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

    button, select {
      padding: 8px;
      font-size: 14px;
    }

    button.saveAll, button.medalsRanksLabels, button.hachayolsLabels {
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>
<body>
<?php
$empty = true;
foreach ($info as $cat => $more) {
    if ($cat == 'status') continue;
    if (!empty($more)) {
        $empty = false;
        break;
    }
}
if ($empty) {
    echo "<h1>No results found</h1>";
    exit;
}
if (in_array($_POST['report_type'], ['all', 'summary'])) {
    $i = 0;
    $s_items = ['Item ID', 'Quantity', 'Item Name', 'Size', 'Gender/Color', 'Category'];
    echo "Order Summary by: <select name='order' id='order'>";
    echo "<option value='-1'>Choose ordering</option>";
    foreach ($s_items as $s_item) {
        echo "<option value='" . $i . ":asc'>" . $s_item . " - Asc</option>";
        echo "<option value='" . $i++ . ":desc'>" . $s_item . " - Desc</option>";
    }
    echo "</select><br /><br />";
}
$idx = 0;
echo "Order Details by: <select name='orderBy' id='orderBy'>";
echo "<option value='-1'>Choose ordering</option>";
foreach ($fields_chosen as $field) {
    if (strpos($field, 'shipping') === false) {
        echo "<option value='" . $idx . ":asc'>" . $fields[$field] . " - Asc</option>";
        echo "<option value='" . $idx++ . ":desc'>" . $fields[$field] . " - Desc</option>";
    }
}
echo "<option value='" . $idx . ":asc'>Item - Asc</option>";
echo "<option value='" . $idx++ . ":desc'>Item - Desc</option>";
foreach ($item_details_chosen as $field) {
    echo "<option value='" . $idx . ":asc'>" . ucwords($field) . " - Asc</option>";
    echo "<option value='" . $idx++ . ":desc'>" . ucwords($field) . " - Desc</option>";
}
echo "</select><br /><br />";
if ($super) {
  echo "<button class='saveAll no-print'>Save All Schools as Shipped</button><br />";
  if ((in_array('medals', $cats) || in_array('ranks', $cats))) {
    echo "<button class='medalsRanksLabels no-print' style='margin-top: 10px;'>Print as Labels</button><br />";
  }
  if (in_array('hachayols', $cats)) {
    // echo "<button class='hachayolsLabels no-print' style='margin-top: 10px;'>Save & Print All Hachayols as Labels</button><br />";
  }
  if (in_array($_POST['report_type'], ['all', 'details'])) {
    echo "<br />Change Shipment Number: <select name='changeShipmentNumber' id='changeShipmentNumber'>";
    for ($i = 1; $i <= 9; $i++) {
      echo "<option value='" . $i . "'>" . $i . "</option>";
    }
    echo "</select><br />";
  }
  echo "<br />";
}
foreach ($resultsBySchool as $school => $more) :
  if (in_array($_POST['report_type'], ['all', 'summary']) && empty($summary[$school])) continue;
  ?>
  <div class="header" id="<?= $school ?>">
      <?php
      if (!isset($schools[$school])) continue;
      echo "<h3>" . $schools[$school] . "</h3>";
      if ($super) echo "<button class='saveSchool no-print'>Save " . $schools[$school] . " as Shipped</button>";
      if ($super && (in_array('medals', $cats) || in_array('ranks', $cats))) {
          echo "<br /><button class='schoolMedalsRanksPrinted no-print' style='margin-top: 10px;' data-school='" . $school . "'>Save " . $schools[$school] . " Medals / Ranks as Printed</button>";
      }
      if ($super && in_array('hachayols', $cats)) {
        echo "<br /><button class='schoolHachayolsPrinted no-print' style='margin-top: 10px;' data-school='" . $school . "'>Save " . $schools[$school] . " Hachayols as Printed</button>";
      }
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
        <table class="table table-striped table-condensed cell-border hover row-order order-column summary">
          <thead>
          <tr>
            <?php foreach ($s_items as $s_item) : ?>
              <th><?= $s_item ?></th>
            <?php endforeach; ?>
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
              echo "<th class='no-print'>Shipment Number</th>";
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
<script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
<script src="/scripts/js.cookie.js"></script>
<script>
  // summary tables don't need to be ordered
  const summary = $(".table.summary").DataTable({
    paging: false
  })

  const table = $(".table").not(".summary").DataTable({
    paging: false
  })

  const sortBy = (table, value) => {
    const info = value.split(':')
    const orderby = info[0]
    const dir = info[1]
    if (orderby >= 0)
      table.order([orderby, dir]).draw();
  }

  if (document.getElementById('order')) {
    document.getElementById('order').addEventListener('change', function () {
      sortBy(summary, this.value)
    })
  }

  if (document.getElementById('orderBy')) {
    document.getElementById('orderBy').addEventListener('change', function () {
      sortBy(table, this.value)
    })
  }

  let info = []
  const super_admin = <?= $super ? 1 : 0; ?>;
  const year = <?= $year; ?>;

  function update(elem, action, desc = '', ship_num = 1) {
    const id = $(elem).attr('id')
    const ids = id.split(':')
    console.log(ids)
    const item = ids[0]
    const user = ids[1]
    const num = ids[2]
    let subject = null, medal = null;
    if (ids.length > 3) {
      subject = ids[3]
      medal = ids[4]
    }
    action = parseInt(action)
    if (action != 4 || (action == 4 && desc)) {
      // find out if item already exists
      const found = info.find(e => e.item == item && e.user == user && e.num == num)
      if (found) {
        found.action = action
        found.desc = desc
        found.ship_num = ship_num
      } else {
        if (ids.length > 3) {
          info.push({ action, item, user, desc, num, ship_num, subject, medal })
        } else {
          info.push({ action, item, user, desc, num, ship_num })
        }
      }
    } else {
      alert('You must explain the damage before it can be saved.')
    }
  }

  function save(reload = true) {
    console.log(info)
    // return false;
    fetch('ajax/saveShipping.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept-Encoding': 'gzip, deflate' // Browser will compress automatically
        },
        body: JSON.stringify({ info, year })
    })
    .then(response => response.json())
    .then(data => {
      console.log(data)
      if (data.success) alert('Saved successfully')
      if (data.success && reload) location.reload()
      else if (!data.success && data.error) alert(data.error)
      if (data.info) console.log(data.info)
    })
    .catch(error => {
      console.log(error)
    })
  }

  $(".saveAll").click(function () {
    $(".shipping").each(function () {
      const action = super_admin ? 1 : 2
      const desc = $(this).parent().parent().find('.description').val()
      const ship_num = $(this).parent().parent().find('.shipment_number').val()
      update(this, action, desc, ship_num)
    })
    save()
  })

  $(".saveSchool").click(function () {
    $(this).parent().find('.shipping').each(function () {
      const action = super_admin ? 1 : 2
      const desc = $(this).parent().parent().find('.description').val()
      const ship_num = $(this).parent().parent().find('.shipment_number').val()
      update(this, action, desc, ship_num)
    })
    save()
  })

  $(".shipping").change(function () {
    const originalVal = $(this).data('original-value')
    const action = parseInt(this.value)
    const desc = $(this).parent().parent().find('.description').val()
    const ship_num = $(this).parent().parent().find('.shipment_number').val()
    if (!super_admin && action == 0) {
      $(this).val(originalVal)
      alert('You cannot change to Not Yet Shipped!')
      return false
    } else if (!super_admin && action == 4) {
      alert('You must explain the damage before it can be saved.')
      return false
    } 
    update(this, action, desc, ship_num)
    save(false)
  })

  $(".shipment_number").blur(function () {
    const ship_num = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    const desc = $(elem).parent().parent().find('.description').val()
    update(elem, action, desc, ship_num)
    save(false)
  })

  $(".description").blur(function () {
    const val = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    const ship_num = $(elem).parent().parent().find('.shipment_number').val()
    update(elem, action, val, ship_num)
    save(false)
  })

  window.onload = function() {
    if (!super_admin) {
      $(".shipment_number").attr('disabled', true)
    } else {
      $("#changeShipmentNumber").change(function () {
        const ship_num = $(this).val()
        $(".shipment_number").val(ship_num)
      })
    }
  }

  $(".medalsRanksLabels").click(function () {
    Cookies.set('for_labels', <?= json_encode($for_labels); ?>);
    // $(".shipping").each(function () {
    //   const action = super_admin ? 1 : 2
    //   const desc = $(this).parent().parent().find('.description').val()
    //   const ship_num = $(this).parent().parent().find('.shipment_number').val()
    //   update(this, action, desc, ship_num)
    // })
    window.open('printLabels.php', '_blank');
  })
</script>
</html>