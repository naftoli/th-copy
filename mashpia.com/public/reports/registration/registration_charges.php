<?php
$admin_auth = array();
require_once(__DIR__ . '/../../header.php');

require_once(__DIR__ . '/../../class.globalSettings.php');
$year = GlobalSettings::getRegistrationYear();

// get the totals
$totals = [];
$total_query = mysql_query(
    "SELECT type, SUM( amount ) AS total 
    FROM registration_charges 
    WHERE year = $year AND refunded = 0 
    GROUP BY type ORDER BY type;"
);
$grand_total = 0;
while ($row = mysql_fetch_assoc($total_query)) {
    $grand_total += intval($row['total']);
    $totals[getDescription($row['type'])] = intval($row['total']);
}
ksort($totals);

// get the details
$details = [];
$detail_query = mysql_query(
    "SELECT s.school_name, s.school_number, u.user_serial, u.first, u.last, rc.registration_charge_id, rc.type, rc.date, 
      rc.year, rc.amount, rc.refunded "
    . "FROM registration_charges rc LEFT JOIN schools s USING ( school_id ) "
    . "LEFT JOIN users u USING ( user_id ) LEFT JOIN transactions t USING ( trans_id ) "
    . "WHERE year = $year ORDER BY rc.date DESC, school_name, u.first, u.last, rc.amount;"
);
while ($row = mysql_fetch_assoc($detail_query)) $details[] = $row;
?>
  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $year ?> Registration Charges</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
      table {
        width: 100%;
      }

      th, td {
        border: 1px solid #888;
        padding: 4px 8px;
      }

      #details {
        font-size: 14px;
      }
    </style>
  </head>
  <body>
  <?php include(__DIR__ . '/../../admin_header.php'); ?>
  <h1><?= $year ?> Soldier Registration Charges</h1>
  <h2>Totals</h2>
  <table>
    <thead>
    <th>Registration Type</th>
    <th>Total Received</th>
    </thead>
    <tbody>
    <?php
    foreach ($totals as $type => $total) { ?>
      <tr>
        <td><?= $type ?></td>
        <td>$<?= number_format($total) ?></td>
      </tr>
    <?php } ?>
    <tr>
      <th>Grand Total</th>
      <th>$<?= number_format($grand_total) ?></th>
    </tr>
    </tbody>
  </table>
  <h2>Details</h2>
  <table id='details'>
    <thead>
    <th colspan='2'>Base</th>
    <th>Serial Number</th>
    <th>Name</th>
    <th>Registration Type</th>
    <th>Registration Type Code</th>
    <th>Registration Time</th>
    <th>Amount Paid</th>
    <th>Refunded</th>
    <th></th>
    </thead>
    <tbody>
    <?php
    foreach ($details as $user) { ?>
      <tr id="<?= $user['registration_charge_id'] ?>">
        <td><?= $user['school_number'] ?></td>
        <td><?= $user['school_name'] ?></td>
        <td><?= $user['user_serial'] ?></td>
        <td><?= $user['first'] . " " . $user['last'] ?></td>
        <td><?= getDescription($user['type']) ?></td>
        <td><?= $user['type'] ?></td>
        <td><?= (new DateTime($user['date']))->format('m/d/Y g:i:sa e'); ?></td>
        <td>$<?= $user['amount'] ?></td>
        <td><?= intval($user['refunded']) ? 'Yes' : 'No'; ?></td>
        <td>
          <button class="refund"
              <?php if (intval($user['refunded'])) echo " disabled"; ?>
          >Refund Charge
          </button>
        </td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
  <dialog>
    <form method="dialog">
      <label>Reason for Refund</label>
      <input type="text" name="reason" id="reason" required />
      <br />
      <input type="checkbox" name="duplicate" id="duplicate" value="1" /> Duplicate Charge
      <br />
      <input type="hidden" name="refund_id" id="refund_id" value="" />
      <input type="hidden" name="amount" id="amount" value="" />
      <input type="hidden" name="type" id="type" value="" />
      <input type="hidden" name="serial" id="serial" value="" />
      <button id="refund" type="submit">Refund</button>
    </form>
  </dialog>
  </body>
  <script>
    $(document).ready(function () {
      $('.refund').click(function () {
        const row = $(this).closest('tr')
        document.getElementById('reason').value = '' // reset
        document.getElementById('duplicate').checked = false // reset
        document.getElementById('refund_id').value = row.attr('id')
        document.getElementById('amount').value = row.find('td:nth-child(4)').text()
        document.getElementById('serial').value = row.find('td:nth-child(3)').text()
        document.getElementById('type').value = row.find('td:nth-child(6)').text()
        document.getElementById('amount').value = row.find('td:nth-child(8)').text()
        const dialog = document.querySelector('dialog')
        dialog.showModal()
      })
      $("#refund").click( function() {
        $.ajax({
          url: 'refund.php',
          type: 'POST',
          data: {
            id: document.getElementById('refund_id').value,
            amount: document.getElementById('amount').value,
            reason: document.getElementById('reason').value,
            type: document.getElementById('type').value,
            serial: document.getElementById('serial').value,
            duplicate: document.getElementById('duplicate').checked ? 1 : 0
          },
          success: function (data) {
            if (data.success) {
              alert('Refund successful.\nYou need to refresh the page to see the updated totals.')
              row.find('td:nth-child(8)').text('Yes');
              row.find('td:nth-child(9) button').attr('disabled', true)
            } else {
              alert('Error: ' + (data.error || data));
              console.log(data.error_msg)
            }
          }
        })
      })
    })
  </script>
  </html>
<?php
// lookup description for registration charges table by codeOnly property
function getDescription($code)
{
    $descriptions = [
        'chayolei' => 'CTH Enrollment',
        'shipping' => 'Shipping Fee (before the codes)',

        'THE' => 'CTH Enrollment',
        'HACH' => 'Hachayol Subscription',

        'THAKUSA' => 'CTH AK Shipping USA',
        'THAKCAN' => 'CTH AK Shipping CAN',
        'THAKINT' => 'CTH AK Shipping INT',

        'THMSUSA' => 'CTH MS Shipping USA',
        'THMSCAN' => 'CTH MS Shipping CAN',
        'THMSINT' => 'CTH MS Shipping INT',

        'LDE' => 'Chidon Enrollment',
        'KHKE' => 'KHK Enrollment',
        'LDE:MYSLDS-10' => 'MyShliach chidon enrollment shipping',
        'LDE:AKLDS-10:AKLDBC-20' => 'Anash Kinder chidon enrollment shipping + bc fee',

        'RRYSD' => 'Chidon Reg Yesod',
        'RRYDA' => 'Chidon Reg Yediah',
        'RRHVN' => 'Chidon Reg Havona / Iyun',
        'RRKHK' => 'Chidon Reg KHK',

        'RRSUSA' => 'Chidon Reg Shipping USA',
        'RRSCAN' => 'Chidon Reg Shipping CAN',
        'RRSINT' => 'Chidon Reg Shipping INT',

        'YB1' => 'Yahadus Book 1',
        'YB2' => 'Yahadus Book 2',
        'YB3' => 'Yahadus Book 3',
        'YB4' => 'Yahadus Book 4',
        'YB5' => 'Yahadus Book 5',
    ];
    return $descriptions[$code];
}