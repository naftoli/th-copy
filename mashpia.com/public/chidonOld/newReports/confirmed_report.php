<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$superAdmin = $admin_user['auth'] == 'super';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();
if (isset($_GET['year'])) $year = $_GET['year'];

$sql = "SELECT * FROM chidon_confirmations WHERE year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $year]);
$confirmations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$info = [];
$sql = "SELECT * FROM chidon_open_reg WHERE year = :year";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute([':year' => $year]);
$open_reg = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($open_reg as $reg) {
  $info[$reg['school_id']] = $reg;
}

$school_ids = [];
foreach ($confirmations as $conf) {
  $school_ids[] = $conf['school_id'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Confirmations Report</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style>
          tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
          }
          input[type="checkbox"] {
            width: 20px;
            height: 20px;
          }
        </style>
    </head>
    <body>
        <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
        <h1>Confirmations Report</h1>
        <div>
            Choose Year: <select name="year" id="year">
                <?php for ($i = 5783; $i < 5789; $i++) {
                    echo "<option value='$i'";
                    if ($year == $i) echo " selected ";
                    echo ">$i</option>";
                }
                ?>
            </select>
            <button id="changeYr">Choose Year</button>
        </div>
        <br />
        <table>
            <tr>
                <th>School</th>
                <th>Confirmed</th>
                <?php if ($superAdmin) : ?>
                <th>Remove Confirmation</th>
                <?php endif; ?>
                <th>Open Registration</th>
                <th>Notes</th>
            </tr>
            <?php
            foreach ($schools as $id => $school) {
                if (empty($school)) continue;
                echo "<tr><td>" . $school . "</td><td>";
                if (in_array($id, $school_ids)) echo "yes";
                else echo "no";
                if ($superAdmin) {
                    echo "</td><td><button class='remove_confirmation' data-school_id='" . $id . "'";
                    if (in_array($id, $school_ids)) {
                        echo " disabled";
                    }
                    echo ">Remove Confirmation</button></td>";
                }
                echo "</td><td>";
                echo "<input type='checkbox' class='open_registration' data-school_id='" . $id . "' " . (isset($info[$id]) && $info[$id]['open_reg'] ? 'checked' : '') . " />";
                echo "</td><td>";
                echo "<textarea class='notes' rows='3' cols='20' data-school_id='" . $id . "'>" . (isset($info[$id]) && $info[$id]['notes'] ? $info[$id]['notes'] : '') . "</textarea>";
                echo "</td></tr>";
            }
            ?>
        </table>
    </body>
    <script>
        $("#changeYr").click( function( e ) {
          e.preventDefault()
          const year = $("#year").val()
          location.href = "confirmed_report.php?year=" + year
        })
        $(".open_registration").click( function( e ) {
          const school_id = this.dataset.school_id
          const confirmed = $(this).is(':checked') ? 1 : 0
          fetch('api/updateConfirmation.php', {
            method: 'POST',
            body: JSON.stringify({ school_id: school_id, value: confirmed, field: 'open_reg' })
          })
          .then( response => response.json() )
          .then( data => {
            console.log(data)
            if (!data.success) {
                alert(data.error)
            }
          })
          .catch( error => console.error('Error:', error) )
        })
        $(".notes").change( function( e ) {
          e.preventDefault()
          const school_id = this.dataset.school_id
          const notes = $(this).val()
          fetch('api/updateConfirmation.php', {
            method: 'POST',
            body: JSON.stringify({ school_id: school_id, value: notes, field: 'notes' })
          })
          .then( response => response.json() )
          .then( data => {
            console.log(data)
            if (!data.success) {
                alert(data.error)
            }
          })
          .catch( error => console.error('Error:', error) )
        })
        <?php if ($superAdmin) : ?>
        $(".remove_confirmation").click( function( e ) {
          e.preventDefault()
          const school_id = this.dataset.school_id
          fetch('api/removeConfirmation.php', {
            method: 'POST',
            body: JSON.stringify({ school_id: school_id })
          })
          .then( response => response.json() )
          .then( data => {
            console.log(data)
            if (!data.success) {
                alert("Failed to remove confirmation.")
            } else {
                alert('Confirmation removed successfully.')
                location.reload()
            }
          })
          .catch( error => console.error('Error:', error) )
        })
        <?php endif; ?>
    </script>
</html>
