<?
$admin_auth = array('school');
require('../../header.php');
require_once('../../class.globalSettings.php');
$year = GlobalSettings::getChidonYear();

if( isset($_GET['debug'])){
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Credit Prizes</title>
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
<h1>Chidon Credit Prizes</h1>
<p style="margin: 20px 10px">
    <a href="./new.php" class="button">New Prize</a>
</p>

<table>
    <tr>
        <th>Image</th>
        <th>Prize</th>
        <th>Quantity</th>
        <th>Credits Needed</th>
        <th>Year</th>
    </tr>

    <?
    $sql = 'SELECT * FROM chidon_credit_prizes where year = ' . $year;
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)) { ?>
        <tr>
            <td>
                <? if ($row['img']) { ?>
                    <img src="<?= $row['img'] ?>" width="50" />
                <? } ?>
            </td>
            <td><?= $row['prize'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $row['credits'] ?></td>
            <td><?= $row['year'] ?></td>
            <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?=$row['chidon_credit_prize_id']?>"> EDIT</a> </td>
            <td> <form action="./delete.php?id=<?=$row['chidon_credit_prize_id']?>" method="post"><input type="submit" value="DELETE"/></form> </td>
        </tr>
    <? }
    ?>
</table>
</body>
</html>