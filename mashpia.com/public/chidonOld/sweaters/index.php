<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

use Illuminate\Database\Capsule\Manager as Capsule;
$sweaters = Capsule::table('chidon_sweaters')->get()

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
            
            <? foreach($sweaters as $sweater) { ?>
                <tr>
                    <td>
                        <? if ($sweater->sweater_picture ) { ?>
                            <img src="<?= $sweater->sweater_picture ?>" width="50" />
                        <? } ?>
                    </td>
                    <td><?= $sweater->sweater_name ?></td>
                    <td><?= $sweater->quantity ?></td>
                    <td><?= $sweater->size ?></td>
                    <td><?= $sweater->gender === 'M' ? 'Boys / Mens' : 'Girls / Womens' ?></td>
                    <td><?= $sweater->price ?></td>
                    <td><?= $sweater->our_price ?> </td>
                    <td> <a class="button" style="padding: 3px 7px;" href="./edit.php?id=<?=$sweater->sweater_id ?>">EDIT</a> </td>
                    <td>
                        <form action="./delete.php" method="post">
                            <input type="submit" value="DELETE"/>
                            <input type="hidden" name="id" value="<?= $sweater->sweater_id ?>" />
                        </form>
                    </td>
                </tr>
            <? } ?>
        </table>
    </body>
</html>