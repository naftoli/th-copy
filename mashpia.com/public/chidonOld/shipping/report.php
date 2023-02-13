<?php
function createHtmlForItem($school, $row) {
    global $info, $detailed_fields, $extra_fields, $items_chosen, $summary;

    foreach ($items_chosen as $cat => $more) {
        if (isset($info[$cat]) && isset($info[$cat][$row['user_id']])) {
            $items = $info[$cat][$row['user_id']];
            foreach ($items as $item) {
                // create new row
                echo "<tr>";
                foreach ($detailed_fields as $field) {
                    if (strpos($field, 'shipping') === false) {
                        $desc = substr($field, strpos($field, '.') + 1);
                        echo "<td>" . $row[$desc] . "</td>";
                    }
                }
                echo "<td>" . $cat . "</td><td>";
                echo (isset($item['qty']) ? $item['qty'] : 1) . "</td><td>";
                if (isset($item['type_of_sweater'])) echo " " . ucwords($item['type_of_sweater']);
                echo " " . $item['item'];
                foreach ($extra_fields as $field) {
                    echo "</td><td>";
                    if (isset($item[$field])) echo $item[$field];
                }
                echo "</td></tr>";

                // update summary
                if (isset($summary[$school][$item['item']])) {
                    if (isset($item['amount'])) $summary[$school][$item['item']] += intval($item['amount']);
                    else $summary[$school][$item['item']]++;
                } else {
                    if (isset($item['amount'])) $summary[$school][$item['item']] = intval($item['amount']);
                    $summary[$school][$item['item']] = 1;
                }
            }
        }
    }
}

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.chidonShipping.php';
require 'data.php';

// chosen fields
$extra_fields = [];
$detailed_fields = [];
$fields_chosen = array_keys($_POST['fields']);
foreach ($fields_chosen as $field) {
    if (strpos($field, '.') !== false) $detailed_fields[] = $field;
    else $extra_fields[] = $field;
}

$cs = new ChidonShipping();

// chosen items
$items_chosen = $_POST['items'];
// get results for chosen items
$info = [];
foreach ($items_chosen as $cat => $itemsPerCat) {
    $listOfItems = array_keys($itemsPerCat);
    $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$nameOfFunc($_POST['gender'], $_POST['school'], $listOfItems);
}
//echo "<pre>"; print_r($info); echo "</pre>";

// find all unique tables to fetch from
$tables = [];
foreach ($fields_chosen as $field) {
  if (strpos($field, '.') !== false) {
    $pos = strpos($field, '.');
    $table = substr($field, 0, $pos);
    if (! in_array($table, $tables)) $tables[] = $table;
  }
}

// build sql statement
$tableAliases = [
    'tc'    => 'join th_chidon tc using (user_id)',
    's'     => 'join schools s on u.school_id = s.school_id',
    'c'     => 'join classes c on c.class_id = u.class_id',
    'cup'   => 'join chidon_user_prizes cup using (user_id) ',
    'cp'    => 'join chidon_prizes cp using (chidon_prize_id) '
];

//********* SELECT **********//
$sql = "SELECT u.user_id, u.school_id ";
foreach ($detailed_fields as $field) $sql .= ", " . $field;
$sql .= " FROM users u ";
foreach ($tables as $table) {
  if ($table == 'u') continue;
  $sql .= $tableAliases[$table] . " ";
}

//********* WHERE *********//
$sql .= " WHERE u.user_registered > 0 ";
if (in_array('tc', $tables)) $sql .= " AND tc.year = " . $year;
if ($_POST['school'] > 0) $sql .= " AND u.school_id = " . $_POST['school'];
if ($_POST['gender'] == 'm') $sql .= " AND u.gender = 'M'";
else if ($_POST['gender'] == 'f') $sql .= " AND u.gender = 'F'";

//******* ORDER BY *********//
$sql .= " ORDER BY u.school_id";
if (in_array('c.class_grade', $fields_chosen)) $sql .= ", c.class_grade";
if (in_array('c.class_sub', $fields_chosen)) $sql .= ", c.class_sub";
if (in_array('u.last', $fields_chosen)) $sql .= ", u.last";
if (in_array('u.first', $fields_chosen)) $sql .= ", u.first";
//echo $sql;

$stmt = $MASHPIA_DB->query($sql);
$results = $stmt->fetchAll();
//echo "<pre>"; print_r($results); echo "</pre>";

$resultsBySchool = [];
foreach ($results as $row) {
    $resultsBySchool[$row['school_id']][] = $row;
}
//echo "<pre>"; print_r($resultsBySchool); echo "</pre>"; exit;
$summary = [];
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
    #header {
      font-size: 14px;
      line-height: 1.4;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <?php foreach ($resultsBySchool as $school => $more) : ?>
    <div id="header">
      <?php
      echo "<h3>" . $schools[$school] . ' - ' . $year . "</h3>";
      $address = '';
      foreach ($detailed_fields as $field) {
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
      echo "<br />" . $address . "<br />";
      ?>
    </div>
    <p></p>
    <?php if (in_array($_POST['report_type'], ['all', 'summary'])) : ?>

    <p></p>
    <?php endif; ?>
    <?php if (in_array($_POST['report_type'], ['all', 'details'])) : ?>
    <table id="table" class="table table-striped table-condensed cell-border hover row-order order-column">
      <thead>
        <tr>
          <?php
          foreach ($detailed_fields as $field) {
            if (strpos($field, 'shipping') === false) echo "<th>" . $fields[$field] . "</th>";
          }
          echo "<th>Category</th><th>Quantity</th><th>Item</th>";
          foreach ($extra_fields as $field) {
            if ($field == 'name') $field = 'name preference';
            echo "<th>" . ucwords($field) . "</th>";
          }
          ?>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($more as $row) {
          if (! in_array($row['class_grade'], ['4', '5', '6', '7', '8', '9'])) continue;
          createHtmlForItem($school, $row);
        }
        ?>
      </tbody>
    </table>
    <?php endif; ?>
    <hr />
    <p></p>
  <?php endforeach; ?>
  <?php if ($admin_user['auth'] == 'super') : ?>
    <h2>Grand Summary</h2>
    <table id="table2" class="table table-striped table-condensed cell-border hover row-order order-column">
      <tr>
        <th>School</th>
        <th>Item</th>
        <th>Total</th>
      </tr>
        <?php
        $totals = [];
        foreach ($summary as $school_id => $more) {
          foreach ($more as $item => $total) {
            echo "<tr><td>" . $schools[$school_id] . "</td><td>". $item . "</td><td>" . $total . "</td></tr>";
            if (isset($totals[$item])) $totals[$item] += $total;
            else $totals[$item] = $total;
          }
        }
        foreach ($totals as $item => $amount) {
          echo "<tr></th><th>Grand Total:</th><th>" . $item . "</th><th>" . $amount . "</th></tr>";
        }
        ?>
    </table>
  <?php endif; ?>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script>
  $('#table').DataTable({
    paging: false
  });
  $('#table2').DataTable({
    paging: false
  });
</script>
</html>