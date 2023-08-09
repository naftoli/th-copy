<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once ( __DIR__ . '/../../header.php' );

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require_once ( __DIR__ . '/../../class.globalSettings.php' ); 
$cur_year = GlobalSettings::getRegistrationYear();
if (isset($_GET['year'])) $year = $_GET['year'];
else $year = $cur_year;

require_once __DIR__ . '/../../class.adminSchools.php';
$s = new AdminSchools($admin_user['admin_id'] ,$admin_user['auth'], true, true);
$all_schools = $s->getSchools();

$types = [
    1 => 'Tuition',
    2 => 'Guaranteed',
    3 => 'Regular'
];

//$main_query = "SELECT s.reg_type, s.school_id, s.school_number, s.school_name, sr.date_paid, sr.amount_paid, total, "
//    ."total_registered, not_chayolei "
//    ."FROM schools s LEFT JOIN school_registrations sr USING (school_id) "
//    ."LEFT JOIN ( "
//        ."SELECT  school_id, COUNT(*) AS total FROM users GROUP BY school_id "
//    .") u USING (school_id) LEFT JOIN ( "
//        ."SELECT school_id, COUNT(*) AS not_chayolei FROM users WHERE chayolei = 0 GROUP BY school_id"
//    .") nc USING (school_id) LEFT JOIN ("
//        ."SELECT school_id, COUNT(*) AS total_registered FROM user_registration WHERE year = $year GROUP BY school_id"
//    .") ur USING (school_id) WHERE ( sr.year = $year OR sr.year IS NULL ) AND s.test_school=0 AND s.chayolei = 1 GROUP BY s.school_id ORDER BY s.school_name";
$main_query = "
    SELECT 
        school_id,
        school_name,
        school_number,
        s.balance, 
        reg_type,
        chayolei_fee,
        chidon_fee,
        school_registration_id,
        date_paid,
        amount_paid, 
        discount,
        total_chayolei,
        total_chidon,
        total_balance_paid,
        total_registered,
        total_chayolei_eligible, 
        total_chidon_eligible
    FROM
        school_registrations sr
            JOIN
        schools s USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_chayolei
        FROM
            school_registration_details
        WHERE
            type = 'chayolei' AND year = $year
        GROUP BY school_id) chayolei USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_chidon
        FROM
            school_registration_details
        WHERE
            type = 'chidon' AND year = $year
        GROUP BY school_id) chidon USING (school_id)
             LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS total_balance_paid 
        FROM
            school_registration_details
        WHERE
            type = 'past_due' AND year = $year
        GROUP BY school_id) balance USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, IFNULL(SUM(amount), 0) AS discount 
        FROM
            school_registration_details
        WHERE
            type = 'discount' AND year = $year
        GROUP BY school_id) discount USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_registered
        FROM
            registration_charges 
        WHERE
            type = 'chayolei' AND year = $year 
        GROUP BY school_id) reg USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_chayolei_eligible
        FROM
            users
        WHERE
            chayolei_eligible = 1
        GROUP BY school_id) chayolei_el USING (school_id)
            LEFT JOIN
        (SELECT 
            school_id, COUNT(*) AS total_chidon_eligible
        FROM
            users
        WHERE
            chidon_eligible = 1
        GROUP BY school_id) chidon_el USING (school_id)
    WHERE
        sr.year = $year 
";
$main_query = mysql_query( $main_query );
$data = [];
while( $row = mysql_fetch_assoc( $main_query ) ) $data[$row['school_name']] = $row;
ksort($data);
//echo "<pre>"; print_r($data); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?=$year?> Registration Charges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<!--    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />-->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
    <style>
        body {
            padding-left: 20px;
            padding-right: 20px;
        }
        .note {
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid black;
            line-height: 1.4;
            margin-bottom: 20px;
        }
        tr, td {
          padding: 5px;
        }
    </style>
</head>
<body>
<!--    --><?php //include( __DIR__ . '/../../admin_header.php'); ?>
    <h1><?=$year?> Base Registration Status</h1>
    <table>
      <tr>
        <td><b>Choose year:</b></td>
        <td>
          <select name="year" id="year">
              <?php
              for ($y = $cur_year; $y >= 5777; $y--) {
                  echo "<option value='$y'";
                  if ($y == $year)
                      echo " selected";
                  echo ">$y</option>";
              }
              ?>
          </select>
      </tr>
    </table>
    <br />
    <form id="add_payment_form">
      <table>
        <tr>
          <td>Add Payment for:</td>
          <td>
            <select name="school_payment" id="school_payment">
              <option value="0">Choose School</option>
              <?php
              foreach ($all_schools as $id => $school) {
                  echo "<option value='$id'>$school</option>";
              }
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Payment type:</td>
          <td>
            <select name="payment_type" id="payment_type">
              <option value="0">Choose Payment Type</option>
              <option value="chayolei">Chayolei</option>
              <option value="chidon">Chidon</option>
              <option value="tanya">Tanya</option>
              <option value="rewards">Rewards Program</option>
              <option value="past_due">Past Dues</option>
            </select>
          </td>
        </tr>
        <tr>
          <td>Payment Method:</td>
          <td>
            <select name="payment_method" id="payment_method">
              <option value="cash">Cash</option>
              <option value="check">Check</option>
              <option value="credit_card">Credit Card</option>
              <option value="wire">Wire Transfer</option>
            </select>
          </td>
        </tr>
        <tr>
          <td>Amount:</td>
          <td>$<input type="text" name="payment_amount" id="payment_amount" /></td>
        </tr>
      </table>
      <button id="add_payment">Add Payment</button>
    </form>
    <br />
    <p class="note">
        Please Note: This report is a <b>financial</b> report, and as such, it shows how many children are in each school based
        on the <b>charges</b>. Therefore, when there are multiple charges for one child (whether they paid twice, or three times, etc),
        it is counted as 2 or 3 kids. As a result, this number may be completely different than the number of registered children
        that is being shown on the home page of the base commander's site, or any other reports. (This can also include
        situations where the child paid, and then "unenrolled" but was never removed from payment database).
    </p>
    <table id="table" class="table table-striped table-condensed">
        <thead>
            <th>Base Type</th>
            <th>Base Number</th>
            <th>Base Name</th>
            <th>Date Registered</th>
            <th>Chayolei Fee</th>
            <th>Chayolei Paid</th>
            <th>Chidon Fee</th>
            <th>Chidon Paid</th>
            <th>Prior Balance</th>
            <th>Prior Balance Paid</th>
            <th>Total Owing</th>
            <th>Discount</th>
            <th>Total Paid</th>
            <th>Current Balance</th>
            <th>Eligible Chayolei Soldiers</th>
            <th>Chayolei Soldiers Registered</th>
        </thead>
        <tbody>
        <?php
        $totals['chayolei_fee'] = 0;
        $totals['chayolei_paid'] = 0;
        $totals['chidon_fee'] = 0;
        $totals['chidon_paid'] = 0;
        $totals['prior_balance'] = 0;
        $totals['prior_balance_paid'] = 0;
        $totals['owing'] = 0;
        $totals['discount'] = 0;
        $totals['total_paid'] = 0;
        $totals['current_balance'] = 0;
        foreach( $data as $base ) {
            if ( !$base['total_registered'] ) $base['total_registered'] = 0;
//                if ( $base['total'] == 1 && $base['not_chayolei'] == 1) continue; ?>
            <tr>
                <td><?= $types[$base['reg_type']] ?></td>
                <td><?= $base['school_number'] ?></td>
                <td><?= $base['school_name'] ?></td>
                <td><?= $base[ 'date_paid' ] ?
                        ( new DateTime($base[ 'date_paid' ]) )->format( 'm/d/Y g:i:s' ) :
                        'Not Registered';
                    ?></td>
                <td><?= $base['chayolei_fee'] ?></td>
                <td><?= $base['total_chayolei'] ?></td>
                <td><?= $base['chidon_fee'] ?></td>
                <td><?= $base['total_chidon'] ?></td>
                <td><?= $base['balance'] ?></td>
                <td><?= $base['total_balance_paid'] ?></td>
                <td>
                    <?php
                    $total_owing = floatval($base['chayolei_fee']) + floatval($base['chidon_fee']) + floatval($base['balance']);
                    echo $total_owing;
                    ?>
                </td>
                <td><?= ($base['discount'] ? $base['discount'] : 0) ?></td>
                <td>
                    <?php
                    $total_paid = floatval($base['total_chayolei']) + floatval($base['total_chidon']) +
                        floatval($base['total_balance_paid']) + ($base['discount'] ? floatval($base['discount']) : 0);
                    $total_paid = $total_paid == 0 ? floatval($base['amount_paid']) : $total_paid;
                    echo $total_paid;
                    ?>
                </td>
                <?php
                $style = '';
                $balance = ($total_owing + $base['discount']) - $total_paid;
                if ($balance > 0) {
                    $style = "background-color: red";
                }
                ?>
                <td style="<?= $style ?>">
                    <?= $balance ?>
                </td>
                <td><?= $base['total_chayolei_eligible'] ?></td>
                <td><?= $base['total_registered'] ?></td>
<!--                    <td>$--><?//= number_format($base['amount_paid'], 0) ?><!--</td>-->
<!--                    <td>--><?//= number_format($base['total_registered']) .' / '. number_format( $base['total'] - $base['not_chayolei'] ) ?><!--</td>-->
<!--                    <td>--><?//= $base['not_chayolei'] ?><!--</td>-->
            </tr>
            <?php
            $totals['chayolei_fee'] += $base['chayolei_fee'];
            $totals['chayolei_paid'] += $base['total_chayolei'];
            $totals['chidon_fee'] += $base['chidon_fee'];
            $totals['chidon_paid'] += $base['total_chidon'];
            $totals['prior_balance'] += $base['balance'];
            $totals['prior_balance_paid'] += $base['total_balance_paid'];
            $totals['owing'] += $total_owing;
            $totals['discount'] += $base['discount'];
            $totals['total_paid'] += $total_paid;
            $totals['current_balance'] += $balance;
        }
        ?>
        </tbody>
        <tfoot>
        <?php
        echo "<tr><th></th><th></th><th></th><th>Totals:</th><th>" . $totals['chayolei_fee'] . "</th><th>" . $totals['chayolei_paid'] .
            "</th><th>" . $totals['chidon_fee'] . "</th><th>" . $totals['chidon_paid'] . "</th><th>" . $totals['prior_balance'] .
            "</th><th>" . $totals['prior_balance_paid'] . "</th><th>" . $totals['owing'] . "</th><th>" . $totals['discount'] .
            "</th><th>" . $totals['total_paid'] . "</th><th>" . $totals['current_balance'] . "</th><th></th><th></th></tr>";
        ?>
        </tfoot>
    </table>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
<script>
    $(function() {
        $("#add_payment").click( function (e) {
            e.preventDefault()
            const school = $("#school_payment").val()
            const method = $("#payment_method").val()
            const type = $("#payment").val()
            const amount = parseFloat($("#payment_amount").val())
            if (school == '0') {
                alert('You must choose a school')
                return
            }
            if (! amount) {
                alert('You must enter an amount!')
                return
            }
            $.post('addPayment.php', { school: school, method: method, type: type, amount: amount }, function(result) {
                const res = JSON.parse(result)
                if (res.success) {
                    alert('Successfully added.')
                    location.reload()
                }
                else alert(res.error)
            })
        })
    })
    $('#table').DataTable({
        paging : false,
        "order": [[ 2, 'asc' ]]
    });

    $("#year").change( function() {
      let y = $(this).val()
      let url = location.href.split('?')[0]
      location.href = url + '?year=' + y
    })
</script>
</html>
