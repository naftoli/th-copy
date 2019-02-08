<?php
$admin_auth = ['school'];
define( "MASHPIA_AUTH_REQUIRED", true );
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php'; 
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();
$year = 5778;
$users = [];
$school_ids = implode(',', array_keys( $schools ));
$stmt = $MASHPIA_DB->prepare("
  SELECT 
      tc.user_id, u.first, u.last, u.school_id, c.class_grade, c.class_sub
  FROM
      users u
          JOIN
      classes c ON u.class_id = c.class_id
          JOIN
      th_chidon tc USING (user_id)
  WHERE
      tc.year = :year AND u.school_id IN ($school_ids)
          AND tc.contestant = 1
  ORDER BY u.school_id, c.class_grade , c.class_sub , u.last , u.first
");
$res = $stmt->execute([
  ':year' => $year
]);
//echo "<pre>"; $stmt->debugDumpParams(); echo "</pre>";
if ( $res ) {
  $rows = $stmt->fetchAll();
  foreach ( $rows as $row ) {
    $users[] = $row;
  }
}
//echo "<pre>"; print_r( $users ); echo "</pre>";
$skipAuth = 0;
if ( isset( $_GET['skipAuth'] ) && $_GET['skipAuth'] == 5779 ) {
  $skipAuth = 1;
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
      tr, th, td {
        font-size: 12px;
        padding: 5px;
      }
    </style>
  </head>
  <body>
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
    <h1>Chidon Drive</h1>
    <?php
    if ( empty( $users ) ) {
      echo "No eligible children found."; 
      exit;
    }
    ?>
    <form id="school_donation_form">
      <table>
        <tr>
          <td>Name on Card</td>
          <td><input type="text" id="cc_name" placeholder="First Last" required /></td>
        </tr>
        <tr>
          <td>Card Number</td>
          <td><input type="text" id="cc_num" placeholder="4565 2315 5698 4578" required /></td>
        </tr>
        <tr>
          <td>Expiry</td>
          <td><input type="text" id="cc_exp" placeholder="MM/YY" size="3" required /></td>
        </tr>
        <tr>
          <td>CVC</td>
          <td><input type="text" id="cc_cvc" placeholder="CVC" size="3" required /></td>
        </tr>
        <tr>
          <td>Donation Amount</td>
          <td>$<input type="text" id="donation_amount" size="5" required /></td>
        </tr>
        <tr>
          <td>Dedication</td>
          <td><input type="text" id="dedication" size="40" placeholder="In honor of..." /></td>
        </tr>
        <tr>
          <td colspan="2"><button>Donate</button></td>
        </tr>
      </table>
      <br />
      <table>
        <thead>
          <tr>
            <?php if ( $admin_user['auth'] == 'super' ) echo "<th>School</th>"; ?>
            <th>Grade</th>
            <th>Student</th>
            <th>Sponsored Amount</th>
          </tr>
        </thead>
        <tbody>
          <?php
          foreach ( $users as $user ) {
            $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
            echo "<tr>";
            if ( $admin_user['auth'] == 'super' ) echo "<td>" . $schools[$user['school_id']] . "</td>";
            echo "<td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . $grade . "</td><td><input type='text' class='user_donation' id='" . $user['user_id'] . "' size='5' /></td></tr>";
          }
          ?>
        </tbody>
      </table>
    </form>
  </body>
  <script>
    $( function() {
      $("form").submit( function( evt ) {
        evt.preventDefault();

        let donation = parseInt( $("#donation_amount").val() );
        if ( isNaN( donation ) ) {
          alert("Donation must be a whole number.");
          $("#donation_amount").focus();
          return false;
        }
        let user_donations = $(".user_donation");
        let user_donation_amounts = [];
        for ( d in user_donations ) {
          let amount = parseInt( user_donations[d].value );
          if ( amount > 350 ) {
            alert("Maximum subsidy allowed per child is $350.");
            $(user_donations[d]).focus();
            return false;
          }
          if ( amount ) {
            user_donation_amounts.push({
              user_id: $(user_donations[d]).attr('id'), 
              amount: amount
            });
          }
        }

        // make sure total amounts of individual users adds up to total donation
        let total = 0;
        for (u in user_donation_amounts) {
          total += user_donation_amounts[u].amount;
        }
        if ( total != donation ) {
          alert("Individual amounts given to children must add up to the total donation being given.");
          return false;
        }

        // send donation and individual amounts to be processed
        let cc = {};
        cc.name = $("#cc_name").val();
        cc.number = $("#cc_num").val();
        cc.exp = $("#cc_exp").val();
        cc.cvc = $("#cc_cvc").val();
        cc.skip = <?=$skipAuth?>;

        let dedication = $("#dedication").val();
        let year = <?=$year?>;
        $.post('ajax/processDonation.php', { year: year, donation: donation, user_donations: user_donation_amounts, cc_info : cc, dedication: dedication }, 
          function( result ) {
            console.log( result );
            alert( result );
        });
      });
    });
  </script>
</html>