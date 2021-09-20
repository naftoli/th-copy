<?php
$admin_auth = array('school');
require_once ( __DIR__ . '/../../header.php' );

require_once ( __DIR__ . '/../../class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

//if ( isset( $_POST['date'] ) && $_POST['date'] ) {
//    if ( $_POST['date'] == 1 ) {
//        $from = '2020-06-01';
//        $to = '2020-09-16';
//    } else if ( $_POST['date'] == 2 ) {
//        $from = '2020-09-16';
//        $to = '2020-09-21';
//    } else if ( $_POST['date'] == 3 ) {
//        $from = '2020-09-21';
//        $to = '2020-10-15';
//    }
//}

$users = [];
$qry = "SELECT count(*) as total, amount, date, s.school_name, u.first, u.last, c.class_grade, c.class_sub, tc.book, a.admin_address1, a.admin_address2, 
    a.admin_city, a.admin_state, a.admin_postal, a.admin_country, a.admin_email "
    ."FROM registration_charges rc JOIN schools s USING (school_id) "
    ."JOIN users u USING (user_id) "
    ."JOIN classes c ON c.class_id = u.class_id "
    ."JOIN th_chidon tc on (tc.user_id = rc.user_id and tc.year = rc.year) "
    ."JOIN admin_auths aa on aa.id = u.user_id "
    ."JOIN admins a on a.admin_id = aa.admin_id "
    ."WHERE type in ('yahadus', 'chidon') "
    ."AND rc.year = " . $year;
// limit to dates if limit exists
if (isset($_POST['fromDate']) && $_POST['fromDate'] && isset($_POST['toDate']) && $_POST['toDate']) {
    $from = mysql_real_escape_string( $_POST['fromDate'] );
    $to = mysql_real_escape_string( $_POST['toDate'] );
}
if ( isset( $from ) && isset( $to ) ) {
    $qry .= " AND rc.date >= '" . $from . " 14:00:00' AND rc.date <= '" . $to . " 13:59:59' ";
}
$qry .= " AND rc.school_id = 269 ";
$qry .= "GROUP BY rc.user_id ORDER BY school_name, first, last, date";
//echo $qry;

$users_query = mysql_query( $qry );
while ( $row = mysql_fetch_assoc( $users_query ) ) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Study Guides / Book Purchases</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        table { width: 100%; }
        th, td { border: 1px solid #888; padding: 4px 8px; }
    </style>
</head>
<body>
<?php include( __DIR__ . '/../../admin_header.php'); ?>
<h1>Study Guides / Book Purchases</h1>
<form action="anashKinder.php" method="post">
    <p>
        To have report based on dates, choose starting and ending dates and then click "Refresh Report"
    </p>
<!--    <p>-->
<!--        <select name="date">-->
<!--            <option value="0">Choose Batch Number</option>-->
<!--            <option value="1"-->
<!--                --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 1 ) echo "selected" ?>
<!--            >1st Batch (until Sept 16)</option>-->
<!--            <option value="2"-->
<!--                --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 2 ) echo "selected" ?>
<!--            >2nd Batch (from Sep 16 until Sep 21)</option>-->
<!--            <option value="3"-->
<!--                --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 3 ) echo "selected" ?>
<!--            >3rd Batch (from Sep 21 to Oct 15)</option>-->
<!--                            <option value="4"-->
<!--                            --><?php //if ( isset( $_POST['date'] ) && $_POST['date'] == 4 ) echo "selected" ?>
<!--                            >4th Batch (from Sept 26 to Oct 25)</option>-->
<!--        </select>-->
<!--    </p>-->
    <p>
<!--        OR-->
        From Date: <input type="date" name="fromDate" />
        To Date: <input type="date" name="toDate" />
    </p>
    <input type="submit" name="submit" value="Refresh Report" />
</form>
<div style="page-break-after: always;"></div>
<?php
// compute totals
$book_totals = [];
$booklet_totals = [];
$schools = ['Anash Kinder'];
foreach ( $schools as $school ) {
    $book_totals[$school] = [
        1   =>  0,
        2   =>  0,
        3   =>  0,
        4   =>  0,
        5   =>  0
    ];
    $booklet_totals[$school] = [
        1   =>  0,
        2   =>  0,
        3   =>  0,
        4   =>  0,
        5   =>  0
    ];
}

foreach ( $users as $user ) {
    $grade = $user['class_grade'];
    $address = $user['admin_address1'] . (empty( $user['admin_address2'] ) ? '' : "<br />" . $user['admin_address2']);
    $address .= "<br />" . $user['admin_city'] . ", " . $user['admin_state'] . " " . $user['admin_postal'];
    $address .= "<br />" . $user['admin_country'];
    //if ( !$schoolTransitioned ) $grade++;
    ?>
    <h2><?=$user[ 'school_name' ]?></h2>
    <table>
        <thead>
        <tr>
            <th>First</th>
            <th>Last</th>
            <th>Grade</th>
            <th>Study Guide #</th>
            <th>Book #</th>
            <th>Date Purchased</th>
            <th>Address</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?= $user[ 'first' ]; ?></td>
            <td><?= $user[ 'last' ]; ?></td>
            <td><?= $grade . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']); ?></td>
            <td><?= $user['book'] ?></td>
            <td><?= $user['total'] > 1 ? $user['book'] : '' ?></td>
            <td><?= ( new DateTime($user[ 'date' ]) )->format( 'm/d/Y' ); ?></td>
            <td><?= $address; ?></td>
        </tr>
        </tbody>
    </table>
    <p><?= $user['admin_email']; ?></p>
    <div style="page-break-after: always;"></div>
    <?php
    $booklet_totals[ $user['school_name'] ][ $user['book'] ]++;
    if ( $user['total'] > 1 ) $book_totals[ $user['school_name'] ][ $user['book'] ]++;
}
foreach ( $schools as $school ) {
    ?>
    <h2>Study Guide Totals for <?= $school ?></h2>
    <table>
        <tr>
            <th>Booklet #</th>
            <th>Total</th>
        </tr>
        <?php
        foreach ( $booklet_totals[$school] as $booklet => $total ) {
            echo "<tr><td>" . $booklet . "</td><td>" . $total . "</td></tr>";
        }
        ?>
    </table>
    <h2>Yahadus Book Totals for <?= $school ?></h2>
    <table>
        <tr>
            <th>Book #</th>
            <th>Total</th>
        </tr>
        <?php
        foreach ( $book_totals[$school] as $book => $total ) {
            echo "<tr><td>" . $book . "</td><td>" . $total . "</td></tr>";
        }
        ?>
    </table>
    <div style="page-break-after: always;"></div>
<?php } ?>
</body>
</html>