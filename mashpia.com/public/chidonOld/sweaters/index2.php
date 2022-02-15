<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

require_once __DIR__ . '/../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Sweaters</title>
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
        <h1>Chidon Sweaters</h1>
        <p style="margin: 20px 10px">
            <a href="./new.php" class="button">New Sweater</a>
        </p>

        <table>
            <tr>
                <th>picture</th>
                <th>name</th>
                <th>Quantity</th>
                <th>Size</th>
                <th>Gender</th>
                <th>Price</th>
                <th>Our Price</th>
            </tr>
            
            <?
                $sql = 'SELECT * FROM chidon_sweaters where year = ' . $year;
                $query = mysql_query($sql);
                while($row = mysql_fetch_assoc($query)) { ?>
                    <tr>
                        <td>
                            <? if ($row['sweater_picture']) { ?>
                                <img src="<?= $row['sweater_picture'] ?>" width="50" />
                            <? } ?>
                        </td>
                        <td><?= $row['sweater_name'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['size'] ?></td>
                        <td><?= $row['gender'] === 'M' ? 'Boys / Mens' : ($row['gender'] === 'F' ? 'Girls / Womens' : "") ?></td>
                        <td><?= $row['price'] ?></td>
                        <td><?= $row['our_price'] ?> </td>
                        <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?=$row['sweater_id']?>"> EDIT</a> </td>
                        <td> <form action="./delete.php?id=<?=$row['sweater_id']?>" method="post"><input type="submit" value="DELETE"/></form> </td>
                    </tr>
                <? }
            ?>
        </table>
    </body>
</html>