<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

$id = isset($_GET['id']) ? $_GET['id'] : false;
if (!$id){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}

use Illuminate\Database\Capsule\Manager as Capsule;

$prize = Capsule::table('chidon_prizes')->where('prize_id', $id)->first();

if (!$prize){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Edit Chidon Prize</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            .form_control {
                padding: 5px 10px;
            }
            .form_control>*:first-child {
                display: inline-block;
                width: 175px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('../../admin_header.php'); ?>
        <h1>Edit Chidon Prize</h1>
        <p style="margin: 20px 10px">
            <a href="./index.php" class="button">Back to All Prizes</a>
        </p>
        <h2><?= $prize->prize_name ?></h2>
        <? if ($prize->prize_picture){ ?>
            <div class="form_control">
                Current Picture: <br><img src="<?= $prize->prize_picture ?>" width="175" /><br><br>
            </div>
        <? } ?>
        <form method="post" action="./update.php" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="<?= $prize->prize_id ?>" required/>

            <div class="form_control">
                <label for="prize_name">Prize Name</label>
                <input type="text" id="prize_name" name="prize_name" value="<?= $prize->prize_name ?>" required/>
            </div>

            <div class="form_control">
                <label for="prize_picture">Prize Picture</label>
                <input type="file" id="prize_picture" name="prize_picture" />
            </div>

            <div class="form_control">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?= $prize->quantity ?>" required/>
            </div>

            <div class="form_control">
                <label for="made_possible_by">Made Possible By</label>
                <input type="text" id="made_possible_by" name="made_possible_by" value="<?= $prize->made_possible_by ?>" />
            </div>

            <div class="form_control">
                <label for="personalization">Personalization</label>
                <input type="text" id="personalization" name="personalization" value="<?= $prize->personalization ?>" />
            </div>

            <div class="form_control">
                <label for="color">Color</label>
                <input type="text" id="color" name="color" value="<?= $prize->color ?>" />
            </div>

            <div class="form_control">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" value="<?= $prize->size ?>" />
            </div>

            <div class="form_control">
                <label for="note">Note</label>
                <input type="text" id="note" name="note" value="<?= $prize->note ?>" />
            </div>

            <div class="form_control">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= $prize->price ?>" required/>
            </div>

            <div class="form_control">
                <label for="our_price">Our Price</label>
                <input type="number" step="0.01" id="our_price" name="our_price" value="<?= $prize->our_price ?>" required/>
            </div>
            <div class="form_control">
                <input type="submit" id="submit" value="Save Changes" />
            </div>
        </form>
    </body>
</html>