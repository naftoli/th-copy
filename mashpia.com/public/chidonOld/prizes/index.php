<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}
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
            </tr>
            
            <?
                $sql = 'SELECT * FROM chidon_prizes WHERE year = 5783';
                $query = mysql_query($sql);
                while($row = mysql_fetch_assoc($query)) { ?>
                    <tr>
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
                        <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?=$row['prize_id']?>"> EDIT</a> </td>
                        <td> <form action="./delete.php?id=<?=$row['prize_id']?>" method="post"><input type="submit" value="DELETE"/></form> </td>
                    </tr>
                <? }
            ?>
        </table>
    </body>
</html>