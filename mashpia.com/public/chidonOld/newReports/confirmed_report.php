<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();
if (isset($_GET['year'])) $year = $_GET['year'];

$sql = "select school_id from chidon_confirmations where year = " . $year;
$res = $mysqli->query($sql);
$confirmations = $res->fetch_all(MYSQLI_ASSOC);

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
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
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
                <td>School</td>
                <td>Confirmed</td>
            </tr>
            <?php
            foreach ($schools as $id => $school) {
                echo "<tr><td>" . $school . "</td><td>";
                if (in_array($id, $school_ids)) echo "yes";
                else echo "no";
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
    </script>
</html>
