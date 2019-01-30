<?php
$admin_auth = ['school'];
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
//echo "<pre>"; $stmt->debugDumpParams(); echo "</pre>";
$res = $stmt->execute([
  ':year' => $year
]);
if ( $res ) {
  $rows = $stmt->fetchAll();
  foreach ( $rows as $row ) {
    $users[] = $row;
  }
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
    if ( empty( $users ) ) echo "No eligible children found."; exit;
    ?>
    <form id="school_donation_form">
      Donation Amount: <input type="text" id="donation_amount" />
      <button>Save</button>
      <br /><br />
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
          return false;
        }
        let user_donations = $(".user_donation");
        let user_donation_amounts = [];
        for ( d in user_donations ) {
          let amount = parseInt( user_donations[d].value );
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
        let year = <?=$year?>;
        $.post('ajax/processDonation.php', { year: year, donation: donation, user_donations: user_donation_amounts }, function( result ) {
          console.log( result );
        });
      });
    });
  </script>
</html>