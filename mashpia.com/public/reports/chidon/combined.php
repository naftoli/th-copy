<?php
$admin_auth = array('school');
require_once ( __DIR__ . '/../../header.php' );

require_once ( __DIR__ . '/../../class.globalSettings.php' );
$year = GlobalSettings::getChidonRegYear();

require_once ( __DIR__ . '/../../class.adminSchools.php' );
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

//if ( isset( $_POST['date'] ) && $_POST['date'] ) {
//    if ( $_POST['date'] == 1 ) {
//        $from = '2020-06-01';
//        $to = '2020-09-16';
//    } else if ( $_POST['date'] == 2 ) {
//        $from = '2020-09-16';
//        $to = '2020-09-22';
//    } else if ( $_POST['date'] == 3 ) {
//        $from = '2020-09-22';
//        $to = '2020-10-15';
//    }
//    $from .= " 14:00:00";
//    $to .= " 21:59:59";
//    } else if ( $_POST['date'] == 4 ) {
//        $from = '2019-09-26';
//        $to = '2019-10-25';
//    }
//}

$combined_users = [];
$qry = "SELECT count(*) as total, amount, date, s.*, logo, first, last, c.class_grade, c.class_sub, tc.book, rc.user_id, 
        rc.study_guide_shipped, rc.book_shipped "
    ."FROM registration_charges rc "
    ."JOIN users u USING (user_id) "
    ."JOIN schools s ON s.school_id = u.school_id "
    ."JOIN classes c ON c.class_id = u.class_id "
    ."JOIN th_chidon tc on (tc.user_id = rc.user_id and tc.year = rc.year) "
    ."WHERE type in ('chidon', 'yahadus') "
    ."AND rc.year = $year ";
// limit to dates if limit exists
if (isset($_POST['fromDate']) && $_POST['fromDate'] && isset($_POST['toDate']) && $_POST['toDate']) {
    $from = mysql_real_escape_string( $_POST['fromDate'] );
    $to = mysql_real_escape_string( $_POST['toDate'] );
}
if ( isset( $from ) && isset( $to ) ) {
    $qry .= "AND rc.date >= '" . $from . "' AND rc.date <= '" . $to . "'";
}
if (isset($_POST['not_shipped'])){
    $qry .= " and book_shipped = 0";
}
$qry .= " AND u.school_id in (" . implode(',', array_keys($schools)) . ") ";
$qry .= "GROUP BY rc.user_id ORDER BY school_name, c.class_grade, c.class_sub, last, first";
echo $qry; exit;
$booklet_users_query = mysql_query( $qry );
while ( $row = mysql_fetch_assoc( $booklet_users_query ) ) {
    $combined_users[$row['school_id']][] = $row;
}

$book_grand_totals = [
    1   =>  0,
    2   =>  0,
    3   =>  0,
    4   =>  0,
    5   =>  0
];

$booklet_grand_totals = [
    1   =>  0,
    2   =>  0,
    3   =>  0,
    4   =>  0,
    5   =>  0
];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Chidon Combined Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
  <style>
    table { width: 100%; }
    th, td { border: 1px solid #888; padding: 4px 8px; }
  </style>
</head>
<body>
<?php include( __DIR__ . '/../../admin_header.php'); ?>
<h1>Chidon Combined Report</h1>
<form action="combined.php" method="post">
  <p>
    To have report based on dates, choose starting and ending dates and then click "Refresh Report"
  </p>
  <!--        <p>-->
  <!--            <select name="date">-->
  <!--                <option value="0">Choose Batch Number</option>-->
  <!--                <option value="1"-->
  <!--                    --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 1 ) echo "selected" ?>
  <!--                >1st Batch (until Sept 16)</option>-->
  <!--                <option value="2"-->
  <!--                    --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 2 ) echo "selected" ?>
  <!--                >2nd Batch (from Sep 16 until Sep 22)</option>-->
  <!--                <option value="3"-->
  <!--                    --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 3 ) echo "selected" ?>
  <!--                >3rd Batch (from Sep 22 to Oct 15)</option>-->
  <!--                                <option value="4"-->
  <!--                                <?php ////if ( isset( $_POST['date'] ) && $_POST['date'] == 4 ) echo "selected" ?>-->
  <!--                                >4th Batch (from Sept 26 to Oct 25)</option>-->
  <!--            </select>-->
  <!--        </p>-->
  <p>
    <!--            OR -->
    From Date: <input type="datetime-local" name="fromDate" />
    To Date: <input type="datetime-local" name="toDate" />
  </p>
  <input type="checkbox" name="not_shipped" value="not_shipped" id="" /> Only show not yet shipped books<br />
  <input type="submit" name="submit" value="Refresh Report" />
</form>
<div style="page-break-after: always;"></div>
<br />
<input type="checkbox" class="checkGuides" /> Mark all study guides as shipped<br />
<input type="checkbox" class="checkBooks" /> Mark all books as shipped<br />
<?php
$totals = [];
$book_totals = [];
$booklet_totals = [];
foreach( $combined_users as $school_id => $users ) {
    $totals[$school_id]['booklets'] = 0;
    $totals[$school_id]['books'] = 0;

    $book_totals[$school_id] = [
        1   =>  0,
        2   =>  0,
        3   =>  0,
        4   =>  0,
        5   =>  0
    ];

    $booklet_totals[$school_id] = [
        1   =>  0,
        2   =>  0,
        3   =>  0,
        4   =>  0,
        5   =>  0
    ];
    $base = $users[0];
    $school_address = $base['shipping_first'] . ' ' . $base['shipping_last'] . "<br />" . $base['shipping_address1'] . ' ' . $base['shipping_address2'] . "<br />" .
        $base['shipping_city'] . ', ' . $base['shipping_state'] . ' ' . $base['shipping_postal'] . "<br />" . $base['shipping_country'];
    ?>
  <h2><?=$base[ 'school_name' ]?></h2>
  Shipping Type: <?= $base['shipping_method'] ?><br /><br />
    <?= $school_address ?><br /><br />
    <?= $base['shipping_requests'] ? $base['shipping_requests'] . "<br /><br />" : ''; ?>
  Principal Email: <?= $base['principal_email'] ?>
  <table>
    <thead>
    <tr>
      <th>First</th>
      <th>Last</th>
      <th>Grade</th>
      <th>Study Guide #</th>
      <th>Shipped</th>
      <th>Book #</th>
      <th>Shipped</th>
      <th>Date Purchased</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach( $users as $user ) {
        $grade = $user['class_grade'];
        $id = $user['user_id'];
        //if ( !$schoolTransitioned ) $grade++;
        ?>
      <tr id="<?=$id?>">
        <td><?= $user[ 'first' ]; ?></td>
        <td><?= $user[ 'last' ]; ?></td>
        <td><?= $grade . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
        <td><?= $user['book']; ?></td>
        <td>
          <input type="checkbox" name="sg_shipped[]" class="sg_shipped"
              <?php if ($user['study_guide_shipped']) echo "checked"; ?>
          />
        </td>
        <td><?= $user['total'] > 1 ? $user['book'] : '' ?></td>
        <td>
            <?php if ($user['total'] > 1) : ?>
              <input type="checkbox" name="book_shipped[]" class="book_shipped"
                  <?php if ($user['book_shipped']) echo "checked"; ?>
              />
            <?php endif; ?>
        </td>
        <td><?= ( new DateTime($user[ 'date' ]) )->format( 'm/d/Y g:i:sa e' ); ?></td>
      </tr>
        <?php
        // totals of school
        $booklet_totals[$school_id][$user['book']]++;
        // totals per school
        $totals[$school_id]['booklets']++;
        // grand totals
        $booklet_grand_totals[$user['book']]++;
        // add to book totals if we have more than one entry for this student
        if ( $user['total'] > 1 ) {
            $book_totals[$school_id][$user['book']]++;
            $totals[$school_id]['books']++;
            $book_grand_totals[$user['book']]++;
        }
    }
    ?>
    </tbody>
  </table>
  <div style="page-break-after: always;"></div>
  <h2>Total Study Guides for <?=$base['school_name'];?></h2>
  <table>
    <tr>
      <th>Study Guide #</th>
      <th>Total</th>
    </tr>
      <?php
      foreach ( $booklet_totals[$school_id] as $booklet => $total ) {
          echo "<tr><td>" . $booklet . "</td><td>" . $total . "</td></tr>";
      }
      ?>
  </table>
  <h2>Total Books for <?=$base['school_name'];?></h2>
  <table>
    <tr>
      <th>Book #</th>
      <th>Total</th>
    </tr>
      <?php
      foreach ( $book_totals[$school_id] as $book => $total ) {
          echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
      }
      ?>
  </table>
  <br /><br />
  Shipping Type: <?= $base['shipping_method'] ?><br /><br />
    <?= $school_address ?><br /><br />
    <?= $base['shipping_requests'] ? $base['shipping_requests'] . "<br /><br />" : ''; ?>
  Principal Email: <?= $base['principal_email'] ?>
  <div style="page-break-after: always;"></div>
    <?php
}
$totals['booklets'] = [
  1 => 0,
  2 => 0,
  3 => 0,
  4 => 0,
  5 => 0
];
$totals['books'] = [
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0
];
?>
<h2>Totals</h2>
<table>
  <thead>
  <tr>
    <th>Base</th>
    <th colspan="5">Study Guides</th>
    <th colspan="5">Books</th>
  </tr>
  <tr>
    <th></th>
      <?php
      for ($j = 0; $j < 2; $j++) { // do this twice
          for ($i = 1; $i <= 5; $i++) {
              echo "<th>$i</th>";
          }
      }
      ?>
  </tr>
  </thead>
  <tbody>
  <?php
  foreach ($booklet_totals as $school => $more) {
      echo "<tr><td>" . $schools[$school] . "</td>";
      foreach ($more as $number => $amount) {
          echo "<td>" . $amount . "</td>";
          $totals['booklets'][$number] += $amount;
      }
      foreach ($book_totals[$school] as $number => $amount) {
          echo "<td>" . $amount . "</td>";
          $totals['books'][$number] += $amount;
      }
      echo "</tr>";
  }
  echo "<tr><th>Grand Totals:</th>";
  foreach ($totals['booklets'] as $amount) echo "<th>" . $amount . "</th>";
  foreach ($totals['books'] as $amount) echo "<th>" . $amount . "</th>";
  echo "</tr>";
//  foreach ( $totals as $school_id => $total ) {
//      ?>
<!--    <tr>-->
<!--      <td>--><?//= $schools[$school_id] ?><!--</td>-->
<!--      <td>--><?//= $total['booklets'] ?><!--</td>-->
<!--      <td>--><?//= $total['books'] ?><!--</td>-->
<!--    </tr>-->
<!--      --><?php
//  }
  ?>
  </tbody>
</table>
<?php if ($admin_user['auth'] == 'super') : ?>
  <h2>Grand Totals</h2>
  <table>
    <tr>
      <th>Study Guide #</th>
      <th>Grand Total</th>
    </tr>
      <?php
      foreach ( $booklet_grand_totals as $booklet => $total ) {
          echo "<tr><td>" . $booklet . "</td><td>" . $total . "</td></tr>";
      }
      ?>
  </table>
  <table>
    <tr>
      <th>Book #</th>
      <th>Grand Total</th>
    </tr>
      <?php
      foreach ( $book_grand_totals as $book => $total ) {
          echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
      }
      ?>
  </table>
<?php endif; ?>
</body>
<script>
  $( function () {
    $(".sg_shipped").click( function () {
      update('study_guide_shipped', this)
    })
    $(".book_shipped").click( function () {
      update('book_shipped', this)
    })
    $(".checkGuides").click( function () {
      if ($(this).is(":checked")) {
        checkAll('sg_shipped')
      }
    })
    $(".checkBooks").click( function () {
      if ($(this).is(":checked")) {
        checkAll('book_shipped')
      }
    })
  })
  function update(name, elem) {
    const id = $(elem).parent().parent().attr('id')
    const checked = $(elem).is(":checked") ? 1 : 0
    $.post('ajax/updateShipping.php', { field: name, user_id: id, value: checked }, function(success) {
      if (! parseInt(success)) {
        alert('Error updating.')
      }
    })
  }
  function checkAll(elem) {
    $("." + elem).each( function () {
      if (!$(this).is(":checked")) {
        $(this).trigger('click')
      }
    })
  }
  <?php if ($admin_user['auth'] != 'super') : ?>
  $("input[type=checkbox]").not("#not_shipped").attr('disabled', true)
  <?php endif; ?>
</script>
</html>