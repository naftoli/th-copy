<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

use Illuminate\Database\Capsule\Manager as Capsule;
$prizes = Capsule::table('chidon_prizes')->get()

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
            
            <? foreach ($prizes as $prize) { ?>
                <tr>
                    <td>
                        <? if ($prize->prize_picture) { ?>
                            <img src="<?= $prize->prize_picture ?>" width="50" />
                        <? } ?>
                    </td>
                    <td><?= $prize->prize_name ?></td>
                    <td><?= $prize->quantity ?></td>
                    <td><?= $prize->made_possible_by ?></td>
                    <td><?= $prize->personalization ?></td>
                    <td><?= $prize->color ?></td>
                    <td><?= $prize->size ?></td>
                    <td><?= $prize->note ?></td>
                    <td><?= $prize->price ?></td>
                    <td><?= $prize->our_price ?> </td>
                    <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?= $prize->prize_id ?>">EDIT</a> </td>
                    <td>
                        <form action="./delete.php" method="post">
                            <input type="submit" value="DELETE"/>
                            <input type="hidden" name="id" value="<?= $prize->prize_id ?>" />
                        </form>
                    </td>
                </tr>
            <? } ?>
        </table>
    </body>
</html>