<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

require_once './class.schoolExceptions.php';
$exceptions = SchoolExceptions::getSchoolExceptions();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Prizes</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('../../admin_header.php'); ?>
        <h1>Chidon Prizes</h1>

        <p style="float: right">
          <button class="button" style="padding: 5px;" id="copy">Copy Prizes from <?= ($year - 1) ?> to <?= $year ?></button>
        </p>

        <p style="margin: 20px 10px">
            <a href="./new.php" class="button">New Prize</a>
        </p>

        <table>
            <tr>
                <th>picture</th>
                <th>name</th>
                <th>Quantity</th>
                <th>Made Possible By</th>
                <th>Personalization</th>
                <th>Color</th>
                <th>Size</th>
                <th>Note</th>
                <th>Price</th>
                <th>Our Price</th>
                <th>School Exceptions</th>
                <th></th>
                <th></th>
            </tr>
            
            <?
                $sql = "SELECT * FROM chidon_prizes WHERE year = $year";
                $query = mysql_query($sql);
                while ($row = mysql_fetch_assoc($query)) {
                    $school_exceptions = isset($exceptions[$row['prize_id']]) ? $exceptions[$row['prize_id']] : [];
                    ?>
                    <tr id="<?= $row['prize_id'] ?>">
                        <td>
                            <? if ($row['prize_picture']) { ?>
                                <img src="<?= $row['prize_picture'] ?>" width="50" />
                            <? } ?>
                        </td>
                        <td><?= $row['prize_name'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['made_possible_by'] ?></td>
                        <td><?= $row['personalization'] ?></td>
                        <td><?= $row['color'] ?></td>
                        <td><?= $row['size'] ?></td>
                        <td><?= $row['note'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td><?= $row['our_price'] ?> </td>
                        <td><input type="text" style="width: 65px;" name="exceptions" id="exceptions" value="<?= implode(',', $school_exceptions) ?>"</td>

                        <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?=$row['prize_id']?>"> EDIT</a> </td>
                        <td> <form action="./delete.php?id=<?=$row['prize_id']?>" method="post"><input type="submit" value="DELETE"/></form> </td>
                    </tr>
                <? }
            ?>
        </table>
    </body>
    <script>
      $( function() {
        $("#copy").click( function() {
          $.ajax({
            url: "./copyPrizes.php",
            data: {
              from: <?= ($year - 1) ?>,
              to: <?= $year ?>
            },
            success: function(result) {
              const res = JSON.parse(result)
              if (res.success) {
                alert("Done!");
                location.reload();
              } else {
                alert("Error!");
              }
            }
          })
        })

        $("#exceptions").blur( function() {
          const exceptions = $(this).val()
          const prize_id = $(this).closest("tr").find("td:first").text()
          $.ajax({
            url: "./updateExceptions.php",
            data: {
              prize_id,
              exceptions
            },
            success: function(result) {
              const res = JSON.parse(result)
              if (res.success) {
                alert("Done!");
              } else {
                alert("Error!");
              }
            }
          })
        })
      })
    </script>
</html>