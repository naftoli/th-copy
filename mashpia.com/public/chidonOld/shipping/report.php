<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = isset($_POST['year']) ? $_POST['year'] : GlobalSettings::getChidonYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.chidonShipping.php';
require 'data.php';

$items_chosen = $_POST['items'];
$fields_chosen = array_keys($_POST['fields']);

$cs = new ChidonShipping();
$cs->setSchools(array_keys($schools));
$children = $cs->getChildren();

// get results for chosen items
$info = [];
foreach ($items_chosen as $cat => $itemsPerCat) {
    $listOfItems = array_keys($itemsPerCat);
    $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$nameOfFunc();
}

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
foreach ($fields_chosen as $field) {
  if (strpos($field, '.') !== false) $sql .= ", " . $field;
}
$sql .= " FROM users u ";
foreach ($tables as $table) {
  if ($table == 'u') continue;
  $sql .= $tableAliases[$table] . " ";
}

//********* WHERE *********//
$sql .= " WHERE u.user_registered > 0 ";
if (in_array('tc', $tables)) $sql .= " AND tc.year = " . $year;
if ($_POST['school'] > 0) $sql .= " AND u.school_id = " . $_POST['school'];
if ($_POST['gender'] == 'm') $sql .= " AND u.gender = '" . $_POST['gender'] . "'";
else if ($_POST['gender'] == 'f') $sql .= " AND u.gender = '" . $_POST['gender'] . "'";

//******* ORDER BY *********//
$sql .= "ORDER BY u.school_id";
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
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shipping Reports</title>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
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
      foreach ($fields_chosen as $field) {
        if (strpos($field, '.') !== false) {
          $pos = strpos($field, '.');
          $desc = substr($field, $pos + 1);
        }
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

    <table>
      <tr>
        <?php
        foreach ($fields_chosen as $field) {
          if (in_array($field, $details)) {
              if (strpos($field, '.') !== false) echo "<th>" . $fields[$field] . "</th>";
          }
        }
        // now show categories
        foreach ($items_chosen as $cat => $other) {
          echo "<th>" . ucwords($cat) . "</th>";
        }
        ?>
      </tr>
        <?php
        foreach ($more as $row) {
          echo "<tr>";
          foreach ($fields_chosen as $field) {
            if (in_array($field, $details)) {
                if (strpos($field, '.') !== false) {
                    $pos = strpos($field, '.');
                    $desc = substr($field, $pos + 1);
                    echo "<td>" . $row[$desc] . "</td>";
                }
            }
          }
          // now show items
          foreach ($items_chosen as $cat => $more) {
              echo "<td>";
              if (isset($info[$cat]) && in_array($row['user_id'], array_keys($info[$cat]))) echo 'yes';
              echo "</td>";
          }
          echo "</tr>";
        }
        ?>
    </table>
    <hr />
    <p></p>
  <?php endforeach; ?>
</body>
</html>