<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$superAdmin = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getRegistrationYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.schoolShipping.php';
require 'data.php';

//$updated = getUpdatedSchools($schools);

$items_chosen = isset($_POST['items']) ? $_POST['items'] : [];
$fields_chosen = array_keys($_POST['fields']);
$item_details_chosen = isset($_POST['details']) ? array_keys($_POST['details']) : [];
$limit_to_status = isset($_POST['status']) ? $_POST['status'] : [];
$report_type = $_POST['report_type'];
$list_of_schools = $_POST['school'];

$cs = new SchoolShipping($year);
// get results for chosen items
$info = [];
foreach ($items_chosen as $cat => $itemsPerCat) {
    $listOfItems = array_keys($itemsPerCat);
    $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$nameOfFunc($list_of_schools, $listOfItems);
}
$info['status'] = $cs->getStatus();
//echo "<pre>"; print_r($info); echo "</pre>"; exit;

//********* SELECT **********//
$sql = "SELECT school_id ";
foreach ($fields_chosen as $field) $sql .= ", " . $field;
$sql .= " FROM schools s ";

//********* WHERE *********//
$sql .= " WHERE 1";
$sql .= " AND s.school_id in (" . implode(",", $list_of_schools) . ")";

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
    if (!isset($schools[$school])) continue;
    createHtmlForItem($school, $row, false);
}

// sort summary
foreach ($summary as $school => $qty) {
    ksort($summary[$school]);
}
ksort($summary);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shipping Reports</title>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/>
  <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/colreorder/1.7.0/css/colReorder.dataTables.min.css"/>
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

    button.saveAll {
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>
<body>
<?php
if (in_array($_POST['report_type'], ['all', 'summary'])) {
    $i = 0;
    $s_items = ['Item ID', 'Quantity', 'Item Name', 'Category'];
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
if ($superAdmin) echo "<button class='saveAll no-print'>Save All Schools as Shipped</button><br /><br />";
foreach ($resultsBySchool as $school => $row) : ?>
  <div class="header" id="<?= $school ?>">
      <?php
      if (!isset($schools[$school])) continue;
      if (!isset($summary[$school])) continue;
      echo "<h3>" . $schools[$school] . ' - ' . $year . "</h3>";
      if ($superAdmin) echo "<button class='saveSchool no-print'>Save " . $schools[$school] . " as Shipped</button>";
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
                if (strpos($id, '*') !== false) {
                  $id = str_replace('*', '<span style="color: red;">*</span>', $id);
                }
                  echo "<tr><td>" . $id . "</td><td>" . $qty . "</td>";
                  $item = $summary_items[$id];
                  echo "<td>" . $item['item'] . "</td>";
                  echo "<td>" . $item['cat'] . "</td></tr>";
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
          <button class='no-print csvDownload' onclick='downloadCSV(this)'>Download as CSV</button>
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
              echo "<th class='no-print'>Number of Items</th>";
              echo "<th class='no-print'>Explain the damage</th>";
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
<script src="//cdn.datatables.net/colreorder/1.7.0/js/dataTables.colReorder.min.js"></script>
<script>
  // summary tables don't need to be ordered
  const summary = $(".table.summary").DataTable({
    paging: false,
    colReorder: true, 
    stateSave: true  
  })

  const table = $(".table").not(".summary").DataTable({
    paging: false,
    colReorder: true, 
    stateSave: true  
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
  const super_admin = <?= $superAdmin ? 1 : 0 ?>;
  const bc = super_admin ? 0 : 1;
  const year = <?= $year ?>;

  function update(elem, action, qty = 1, desc = '') {
    const id = $(elem).attr('id')
    const ids = id.split(':')
    const item = ids[0]
    const school = ids[1]
    action = parseInt(action)
    if (
      (action == 3 && qty)
      ||
      (action == 4 && qty && desc)
      ||
      [0, 1, 2, 5].includes(action)
    ) {
      // check if item already exists in array
      let found = false
      for (let i = 0; i < info.length; i++) {
        if (info[i].item == item && info[i].school == school) {
          found = true
          info[i].action = action
          info[i].qty = qty
          info[i].desc = desc
          break
        }
      }
      if (!found) info.push({action, item, school, qty, desc})
    } else {
      alert('Could not update item ' + item + ' for school ' + school)
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
      else if (!data.success) {
        alert(data.error)
        console.log(data.error_info)
      }
      if (data.success && reload) location.reload()
    })
    .catch(error => {
      console.log(error)
    })
  }

  $(".saveAll").click(function () {
    $(".shipping").each(function () {
      const originalVal = parseInt($(this).data('original-value'))
      const action = super_admin ? ([3, 4].includes(originalVal) ? 5 : 1) : 2
      let qty = $(this).parent().parent().find('td:eq(3)').text()
      update(this, action, qty)
    })
    save()
  })

  $(".saveSchool").click(function () {
    $(this).parent().find('.shipping').each(function () {
      const originalVal = parseInt($(this).data('original-value'))
      const action = super_admin ? ([3, 4].includes(originalVal) ? 5 : 1) : 2
      const qty = $(this).parent().parent().find('td:eq(3)').text()
      update(this, action, qty)
    })
    save()
  })

  $(".shipping").change(function () {
    const originalVal = $(this).data('original-value')
    const action = parseInt(this.value)
    const qty = parseInt($(this).parent().parent().find('.qty').val())
    const desc = $(this).parent().parent().find('.description').val()
    if (bc && action == 0) {
      $(this).val(originalVal)
      alert('You cannot change to Not Yet Shipped!')
      return false
    }
    if (action == 4 && !(qty && desc)) {
      // $(this).val(originalVal)
      alert('You must enter how many items are damaged AND explain the damage before it can be saved.')
      return false
    }
    update(this, action, qty)
    save(false)
  })

  $(".qty").blur(function () {
    const val = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    const desc = $(this).parent().parent().find('.description').val()
    if (action == 4 && !desc) {
      alert('You must explain the damage before it can be saved.')
      return false
    }
    update(elem, action, val, desc)
    save(false)
  })

  $(".description").blur(function () {
    const val = $(this).val()
    const elem = $(this).parent().parent().find('.shipping')
    const action = parseInt($(elem).val())
    const qty = $(this).parent().parent().find('.qty').val()
    if (parseInt(qty) == 0) {
      alert('You must enter a qty before it can be saved.')
      return false
    }
    update(elem, action, qty, val)
    save(false)
  })

  $(".updated").click(function () {
    const school_id = $(this).parent().parent().attr('id')
    const checked = $(this).is(":checked") ? 1 : 0
    $.post('ajax/updateSchool.php', {school_id, checked}, function (result) {
      const res = JSON.parse(result)
      if (!res.success) alert(res.error)
    })
  })

  function downloadCSV(btn) {
    // table is the next sibling of the button (h3, button, table in DOM)
    const table = $(btn).next('div').find('table')
    if (!table.length) {
      alert('Could not find table')
      return
    }
    const headers = table.find('th').map((i, el) => $(el).text().trim()).get()
    const rows = table.find('tbody tr').map((i, tr) => {
      return $(tr).find('td').map((j, cell) => {
        const $cell = $(cell)
        // Check if the cell contains a select dropdown
        const $select = $cell.find('select')
        if ($select.length) {
          // If present, get the selected option's text
          return $select.find('option:selected').text().trim()
        } else {
          return $cell.text().trim()
        }
      }).get().join(',')
    }).get()
    const csv = headers.join(',') + '\n' + rows.join('\n')
    // create a blob
    const blob = new Blob([csv], { type: 'text/csv' });
    // create a url
    const url = URL.createObjectURL(blob);
    // create a link
    const link = document.createElement('a');
    link.href = url;
    link.download = 'school_shipping.csv';
    link.click();
  }
</script>
</html>