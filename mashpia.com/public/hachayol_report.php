<?php
ini_set('display_errors', 1);
$admin_auth = array('school');
require('header.php');

// authenticate and only allow super users
if ($admin_user['auth'] != 'super') {
    die('You are not authorized to view this page');
}

// get the number of posters for each school
$posters = [];
$sql = "SELECT * FROM posters";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $posters[$row['school_id']] = $row;
}

$poster_types = [
    'cth_boys'  => 'Chayolei Boys',
    'cth_girls' => 'Chayolei Girls',
    'cth_both'  => 'Chayolei Both',
    'chidon_boys' => 'Chidon Boys',
    'chidon_girls'  => 'Chidon Girls',
    'chidon_both'  => 'Chidon Both'
];
// initialize totals
foreach ($poster_types as $type => $name) {
    $poster_totals[$type] = 0;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <link href="admin_styles.css" rel="stylesheet" type="text/css">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Hachayol Report</title>
  <style type='text/css'>
    .info {
      font-size: 14px;
      line-height: 1.3;
    }

    .page-break {
      page-break-after: always;
    }

    .students {
      margin-left: 50px;
    }

    @media print {
      .hide {
        display: none;
      }
    }

    th, tr, td {
      padding: 10px;
      font-size: 14px;
      border-bottom: 1px solid #cccccc;
    }
  </style>
</head>

<body>
<?php include('admin_header.php'); ?>
<h1 class='hide'>Hachayol Report</h1>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/class.hachayol.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/reports/shipping/functions/get_hachayols.php'); // load the new hachayol shipping functions....
$h = new Hachayol;

//find out if admin is super
if ($admin->auth == 'super') {
    $h->setSchools();
} else {
    $h->setSchools($admin->school_id);
}

$schools = $h->getSchools();
$h->setChidonNumbers(); // find out the chidon eligible children

//variables for grand totals
$grandTotal = 0;
$totals['pickup'] = 0;
$totals['deliver'] = 0;

// order schools by total
$orderedSchools = [];
foreach ($schools as $type => $info) {
    foreach ($info as $id => $school) {
//            if ($id != 162) $total = $school['total'] + $school['teachers'] + get_extra_hachayols($id, $school['total'] + $school['teachers']);
//            else $total = $school['total'] + get_extra_hachayols($id, $school['total']);
        $total = intval($school['total']) + intval($school['teachers']);
        $orderedSchools[$total][$type][$id] = $school;
    }
}
// sort by total
krsort($orderedSchools);
//    echo "<pre>"; print_r($orderedSchools); echo "</pre>";
?>
<div align='center' class='hide'>
  <input type='button' value='Print' onclick='window.print();'/>
</div>
<?php
$teacher_totals = [];
$children_totals = [];
foreach ($orderedSchools as $total => $more) {
    foreach ($more as $type => $other) {
        foreach ($other as $school_id => $school) {
            $grandTotal += $total;
            $totals[$type] += $total;
//                $chidonNum = $h->getChidonNumber( $id );
            if ($type == 'pickup') echo "<h2>For Pickup</h2>";
            else if ($type == 'deliver') echo "<h2>For Delivery</h2>";
            echo "<div class='info'>";
            echo $school['shipping_name'] . "<br />";
            echo $school['name'] . "<br />";
            echo $school['address'] . "<br />";
            echo "Type of school: " . $school['type'] . "<br />";
            echo "Principal: " . $school['principal'] . "<br />";
            foreach ($school['admins'] as $admin) {
                $admin = trim($admin);
                if (!empty($admin)) echo "Admin: " . $admin . "<br />";
            }
            echo "Total Teachers: " . $school['teachers'] . "<br />";
            echo "Total children getting Hachayol: " . $school['total'] . "<br />";

//                if ($id == 162) $extra = get_extra_hachayols($id, $school['total']);
//                else $extra = get_extra_hachayols($id, $school['teachers'] + $school['total']);
//                echo "Total: "  . $school['total'] . "; Extra: " . $extra . "<br />";
            ?>
          <span style="font-size: 50px; font-weight: bold;">Total: <?= $total ?></span><br/>
          Total Registered Children: <?= $school['totalReg'] ?><br/>
          Total Registered for Chidon: <?= $school['chidonReg'] ?><br/>
          Number of Chayolei Boy Posters: <?= $posters[$school_id]['chayolei_b'] ?><br/>
          Number of Chayolei Girl Posters: <?= $posters[$school_id]['chayolei_g'] ?><br/>
          Number of Chayolei Boy/Girl Posters: <?= $posters[$school_id]['chayolei_bg'] ?><br/>
          Number of Chidon Boy Posters: <?= $posters[$school_id]['chidon_b'] ?><br/>
          Number of Chidon Girl Posters: <?= $posters[$school_id]['chidon_g'] ?><br/>
          Number of Chidon Boy/Girl Posters: <?= $posters[$school_id]['chidon_bg'] ?><br/>
          <!--                Possible Chidon Children: --><?php //=$chidonNum?><!--<br />-->
          Shipping Requests: <?= $school['shipping_requests'] ?><br/><br/>
          </div>
          <div class='page-break'></div>
            <?php
            $teacher_totals[$school_id] = $school['teachers'];
            $children_totals[$school_id] = $school['total'];
            // poster totals
            $poster_totals['cth_boys'] += intval($posters[$school_id]['chayolei_b']) ?? 0;
            $poster_totals['cth_girls'] += intval($posters[$school_id]['chayolei_b']) ?? 0;
            $poster_totals['cth_both'] += intval($posters[$school_id]['chayolei_bg']) ?? 0;
            $poster_totals['chidon_boys'] += intval($posters[$school_id]['chidon_b']) ?? 0;
            $poster_totals['chidon_girls'] += intval($posters[$school_id]['chidon_g']) ?? 0;
            $poster_totals['chidon_both'] += intval($posters[$school_id]['chidon_bg']) ?? 0;
        }
    }
}
?>
<h2>Totals</h2>
Total for Pickup: <?= $totals['pickup'] ?><br/>
Total for Delivery: <?= $totals['deliver'] ?><br/>
Grand Total: <?= $grandTotal ?>
<hr/>

<h2>Total Details</h2>
<table>
  <tr>
    <th>School</th>
    <th>Total Children</th>
    <th>Total Teachers</th>
  </tr>
    <?php
    $total_children = 0;
    $total_teachers = 0;
    foreach ($children_totals as $id => $total) {
        echo "<tr><td>" . $id . "</td><td>" . $total . "</td><td>" . $teacher_totals[$id] . "</td></tr>";
        $total_children += $total;
        $total_teachers += $teacher_totals[$id];
    }
    echo "<tr><th>Totals: </th><th>" . $total_children . "</th><th>" . $total_teachers . "</th></tr>";
    ?>
</table>
<hr />
<br />
<h2>Poster Totals</h2>
<table>
  <tr>
    <th>Chayolei Boys</th>
    <th>Chayolei Girls</th>
    <th>Chayolei Both</th>
    <th>Chidon Boys</th>
    <th>Chidon Girls</th>
    <th>Chidon Both</th>
  </tr>
    <?php
    echo "<tr>";
    foreach ($poster_types as $type => $name) {
        echo "<td>" . $poster_totals[$type] . "</td>";
    }
    echo "</tr>";
    ?>
</table>
</body>
</html>
