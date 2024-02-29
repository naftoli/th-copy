<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$superAdmin = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonYear();

require 'class.parentShipping.php';
require 'data.php';

$items_chosen = isset($_POST['items']) ? $_POST['items'] : [];
//$fields_chosen = array_keys($_POST['fields']);
$item_details_chosen = isset($_POST['details']) ? array_keys($_POST['details']) : [];
$limit_to_status = isset($_POST['status']) ? $_POST['status'] : [];
$report_type = $_POST['report_type'];

$cs = new ParentShipping();
// get results for chosen items
$info = [];
foreach ($items_chosen as $cat => $itemsPerCat) {
    $listOfItems = array_keys($itemsPerCat);
    $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$nameOfFunc($listOfItems);
}
$info['status'] = $cs->getStatus();

$parents = $cs->getParents();

$summary = []; // for HQ
$summary_items = []; // mapping of item ID to item info

// go through it once so that we can have totals
foreach ($info as $cat => $more) {
    foreach ($more as $admin_id => $other) {
        createHtmlForItem($admin_id, false);
    }
}
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
<?php if ($superAdmin) : ?>
  <button id="saveAll" class="no-print">Save All Parents as Shipped</button>
<?php endif; ?>
<?php
foreach ($info as $cat => $more) {
    if ($cat == 'status') break;
    if (in_array($_POST['report_type'], ['all', 'summary'])) {
      ?>
      <h3>Summary</h3>
      <table class="table table-striped table-condensed cell-border hover row-order order-column">
        <thead>
        <tr>
          <th>Item ID</th>
          <th>Quantity</th>
          <th>Item Name</th>
          <th>Sweater Type</th>
          <th>Size</th>
          <th>Category</th>
          <!--            <th>Status</th>-->
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($summary as $id => $qty) {
          $item = $summary_items[$id];
          echo "<tr><td>" . $id . "</td><td>" . $qty . "</td>";
          echo "<td>" . $item['item'] . "</td>";
          echo "<td>" . $item['type'] . "</td>";
          echo "<td>" . $item['size'] . "</td>";
          echo "<td>" . $item['cat'] . "</td></tr>";
        }
        ?>
        </tbody>
      </table>
      <p class="no-print"></p>
      <div style="page-break-after: always"></div>
    <?php
    }
    if (in_array($_POST['report_type'], ['all', 'details'])) {
        foreach ($more as $admin_id => $items) {
            $row = $parents[$admin_id];
            $address = $row['address'] . '<br />' . $row['city'] . ', ' . $row['state'] . ' ' . $row['zip'] . '<br />' . $row['country'];
            echo "<div class='header' id='" . $admin_id . "'>";
            echo "<h2>Deliver to:</h2>";
            echo "<p>" . $row['first'] . ' ' . $row['last'] . "<br />" . $address . "</p>";
            ?>
        <!--      <p>-->
        <!--        <input type='checkbox' class='updated' value='--><?php //= $updated[$school] ?><!--'-->
        <!--        --><?php //if (intval($updated[$school]) == 1) echo "checked"; ?>
        <!--        /> I have reviewed and updated the shipping status for the entire school.-->
        <!--      </p>-->
            <table class="table table-striped table-condensed cell-border hover row-order order-column">
              <thead>
              <tr>
                  <?php
                  echo "<th>Item</th>";
                  if ($item_details_chosen && count($item_details_chosen)) {
                      foreach ($item_details_chosen as $field) {
                          if ($field == 'item') continue;
                          if ($field == 'cat') $field = 'category';
                          else if ($field == 'id') $field = 'Item ID';
                          echo "<th>" . ucwords($field) . "</th>";
                      }
                  }
                  echo "<th>Type of Sweater</th>";
                  echo "<th>Size</th>";
                  echo "<th class='no-print'>Status</th>";
                  echo "<th class='no-print'>Number of Items</th>";
                  echo "<th class='no-print'>Explain the damage</th>";
                  ?>
              </tr>
              </thead>
              <tbody>
              <?php createHtmlForItem($admin_id); ?>
              </tbody>
            </table>
            <p class="no-print"></p>
            <div style="page-break-after: always"></div>
            </div>
            <?php
        }
    }
}
?>
<?php //ksort($grand_summary); ?>
<?php
//if ($admin_user['auth'] == 'super' && isset($_POST['grand_summary']) && $_POST['grand_summary'] == 1) {
//    foreach ($grand_summary as $item => $more) {
//        $grand_total = 0;
//        $item_details = $summary_items[$item];
//        $desc = "($item)";
//        foreach (['item', 'size', 'color'] as $attr) {
//            if (isset($item_details[$attr])) $desc .= ' ' . $item_details[$attr];
//        }
//        echo "<br />";
//        echo "<h2>" . ucwords($desc) . " Totals</h2>";
//        ?>
<!--      <table class="table table-striped table-condensed cell-border hover row-order order-column grandTotal">-->
<!--        <thead>-->
<!--        <tr>-->
<!--          <th>School</th>-->
<!--          <th>Total</th>-->
<!--        </tr>-->
<!--        </thead>-->
<!--        <tbody>-->
<!--        --><?php
//        foreach ($more as $school_id => $total) {
//            echo "<tr><td>" . $school_id . "</td><td>" . $total . "</td></tr>";
//            $grand_total += intval($total);
//        }
//        ?>
<!--        </tbody>-->
<!--        <tfoot>-->
<!--        <tr>-->
<!--          <th>Grand Total:</th>-->
<!--          <th>--><?php //= $grand_total; ?><!--</th>-->
<!--        </tr>-->
<!--        </tfoot>-->
<!--      </table>-->
<!--      <div style="page-break-after: always;"></div>-->
<!--        --><?php
//    }
//}
?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
  $('.table').DataTable({
    paging: false
  });

  let info = []

  function update(elem, action, qty = 1, desc = '') {
    const id = $(elem).attr('id')
    const ids = id.split(':')
    const item = ids[0]
    const school = ids[1]
    // get description
    action = parseInt(action)
    if (
      action < 2
      ||
      action == 2 && qty
      ||
      action == 3 && qty && desc
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
    }
  }

  function save(reload = true) {
    $.post('ajax/saveShipping.php', {info}, function (result) {
      const res = JSON.parse(result)
      if (res.success) {
        if (reload) location.reload()
      }
      else alert(res.error)
    })
  }

  $("#saveAll").click(function () {
    $(".shipping").each(function () {
      let qty = $(this).parent().parent().find('td:eq(3)').text()
      update(this, 1, qty)
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
    const qty = parseInt($(this).parent().parent().find('.qty').val())
    const desc = $(this).parent().parent().find('.description').val()
    // if (action == 2 && !qty) {
    //   alert('You must enter how many items are missing before it can be saved.')
    //   return false
    // } else
    if (action == 3 && !(qty && desc)) {
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
    if (action == 3 && !desc) {
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

  // $(".updated").click(function () {
  //   const school_id = $(this).parent().parent().attr('id')
  //   const checked = $(this).is(":checked") ? 1 : 0
  //   $.post('ajax/updateSchool.php', {school_id, checked}, function (result) {
  //     const res = JSON.parse(result)
  //     if (!res.success) alert(res.error)
  //   })
  // })
  /*
    $(function () {
  //    <?php //if (!$superAdmin) : ?>
      $(".shipping").attr('disabled', true)
      $(".qty").attr('disabled', true)
      $(".description").attr('disabled', true)
//    <?php //endif; ?>
  })
*/
</script>
</html>