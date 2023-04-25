<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.schoolShipping.php';
require 'data.php';

$updated = getUpdatedSchools($schools);

$items_chosen = isset($_POST['items']) ? $_POST['items'] : [];
$fields_chosen = array_keys($_POST['fields']);
$item_details_chosen = isset($_POST['details']) ? array_keys($_POST['details']) : [];
$limit_to_status = isset($_POST['status']) ? $_POST['status'] : [];
$report_type = $_POST['report_type'];

$cs = new SchoolShipping();
// get results for chosen items
$info = [];
foreach ($items_chosen as $cat => $itemsPerCat) {
    $listOfItems = array_keys($itemsPerCat);
    $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$nameOfFunc($_POST['school'], $listOfItems);
}
$info['status'] = $cs->getStatus();
//echo "<pre>"; print_r($info); echo "</pre>"; exit;

//********* SELECT **********//
$sql = "SELECT school_id ";
foreach ($fields_chosen as $field) $sql .= ", " . $field;
$sql .= " FROM schools s ";

//********* WHERE *********//
$sql .= " WHERE 1";
if ($_POST['school'] > 0) $sql .= " AND s.school_id = " . $_POST['school'];

//******* ORDER BY *********//
$sql .= " ORDER BY s.school_id";

$stmt = $MASHPIA_DB->query($sql);
$results = $stmt->fetchAll();

$resultsBySchool = [];
foreach ($results as $row) {
    $resultsBySchool[$row['school_id']] = $row;
}

$summary = []; // for schools
$grand_summary = []; // for HQ
$summary_items = []; // mapping of item ID to item info

// go through it once so that we can have totals
foreach ($resultsBySchool as $school => $row) {
    if (! isset($schools[$school])) continue;
    createHtmlForItem($school, $row, false);
}

// sort summary
foreach ($summary as $school => $more) ksort($summary[$school]);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shipping Reports</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
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
  <?php if ($super) : ?>
    <button id="saveAll" class="no-print">Save All Schools as Shipped</button>
  <?php endif; ?>
  <?php foreach ($resultsBySchool as $school => $row) : ?>
    <div class="header" id="<?=$school?>">
      <?php
      if (! isset($schools[$school])) continue;
      if (! isset($summary[$school])) continue;
      echo "<h3>" . $schools[$school] . ' - ' . $year . "</h3>";
      if ($super) echo "<button class='saveSchool no-print'>Save " . $schools[$school] . " as Shipped</button>";
      $address = '';
      foreach ($fields_chosen as $field) {
        $pos = strpos($field, '.');
        $desc = substr($field, $pos + 1);
        switch ($field) {
          case 's.shipping_first':
          case 's.shipping_last':
            $address .= $row[$desc] . ' ';
            break;
          case 's.shipping_phone':
            $address = "Contact Phone Number: " . $row[$desc] . "<br />";
            break;
          case 's.shipping_address1':
          case 's.shipping_address2':
          case 's.shipping_city':
          case 's.shipping_state':
          case 's.shipping_postal':
          case 's.shipping_country':
            $address .= $row[$desc] . ' ';
            break;
          case 's.shipping_requests':
            $address .= "<br />Shipping Requests: " . $row[$desc];
            break;
        }
      }
      if (! empty($address)) echo "<br />" . $address . "<br />";
      ?>
      <p>
        <input type='checkbox' class='updated' value='<?= $updated[$school] ?>'
        <?php if (intval($updated[$school]) == 1) echo "checked"; ?>
        /> I have reviewed and updated the shipping status for the entire school.
      </p>
      <?php if (in_array($_POST['report_type'], ['all', 'summary'])) : ?>
        <h3>Summary</h3>
        <table class="table table-striped table-condensed cell-border hover row-order order-column">
          <thead>
            <tr>
              <th>Item ID</th>
              <th>Quantity</th>
              <th>Item Name</th>
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
                  foreach (['item', 'cat'] as $attr) {
                     echo "<td>";
                     if (isset($item[$attr])) echo $item[$attr];
                     echo "</td>";
                  }
                  echo "</tr>";
                  // add to grand total
                  if (isset($grand_summary[$id][$school])) $grand_summary[$id][$school] += $qty;
                  else $grand_summary[$id][$school] = $qty;
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
                    if ($field == 'item') continue;
                    if ($field == 'cat') $field = 'category';
                    else if ($field == 'id') $field = 'Item ID';
                    echo "<th>" . ucwords($field) . "</th>";
                }
            }
            echo "<th class='no-print'>Status</th>";
            echo "<th class='no-print'>Number of Missing Items</th>"
            ?>
          </tr>
        </thead>
        <tbody>
          <?php createHtmlForItem($school, $row); ?>
        </tbody>
      </table>
      <p class="no-print"></p>
      <div style="page-break-after: always"></div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php ksort($grand_summary); ?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
  $('.table').DataTable({
    paging: false
  });

  let info = []
  const super_admin = <?= $super ? 1 : 0; ?>;

  function update(elem, action, qty = '') {
    const id = $(elem).attr('id')
    const ids = id.split(':')
    const item = ids[0]
    const school = ids[1]
    // get description
    info.push({ action, item, school, qty })
  }

  function save(reload = true) {
    $.post('ajax/saveShipping.php', { info }, function (result) {
      const res = JSON.parse(result)
      if (res.success) {
        if (reload) location.reload()
      }
      else alert(res.error)
    })
  }

  $("#saveAll").click( function () {
    $(".shipping").each( function () {
      update(this, 1)
    })
    save()
  })

  $(".saveSchool").click( function() {
    $(this).parent().find('.shipping').each( function () {
      update(this, 1)
    })
    save()
  })

  $(".shipping").change( function () {
    const action = parseInt(this.value)
    if (!super_admin && action == 0) {
      alert('You cannot change this to not yet shipped, it will not be saved.')
      return false
    } else if (!super_admin && action == 2) {
      alert('You must enter the number of missing items before it can be saved.')
      return false
    }
    update(this, action)
    save(false)
  })

  $(".qty").blur( function() {
    const val = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    update(elem, action, val)
    save(false)
  })

  $(".updated").click( function() {
    const school_id = $(this).parent().parent().attr('id')
    const checked = $(this).is(":checked") ? 1 : 0
    $.post('ajax/updateSchool.php', { school_id, checked }, function (result) {
      const res = JSON.parse(result)
      if (!res.success) alert(res.error)
    })
  })
</script>
</html>