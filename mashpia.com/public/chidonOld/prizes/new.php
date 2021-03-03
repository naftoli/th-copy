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
        <title>New Chidon Prize</title>
        <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            .form_control {
                padding: 5px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('../../admin_header.php'); ?>
        <h1>New Chidon Prize</h1>
        <p style="margin: 20px 10px">
            <a href="./index.php" class="button">Back to All Prizes</a>
        </p>
        <form method="post" action="./create.php" enctype="multipart/form-data">

            <div class="form_control">
                <label for="prize_name">Prize Name</label>
                <input type="text" id="prize_name" name="prize_name" required/>
            </div>

            <div class="form_control">
                <label for="prize_picture">Prize Picture</label>
                <input type="file" id="prize_picture" name="prize_picture" />
            </div>

            <div class="form_control">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="0" required/>
            </div>

            <div class="form_control">
                <label for="made_possible_by">Made Possible By</label>
                <input type="text" id="made_possible_by" name="made_possible_by"/>
            </div>

            <div class="form_control">
                <label for="personalization">Personalization</label>
                <input type="text" id="personalization" name="personalization"/>
            </div>

            <div class="form_control">
                <label for="color">Color</label>
                <input type="text" id="color" name="color"/>
            </div>

            <div class="form_control">
                <label for="size">Size</label>
                <input type="text" id="size" name="size"/>
            </div>

            <div class="form_control">
                <label for="note">Note</label>
                <input type="text" id="note" name="note"/>
            </div>

            <div class="form_control">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="0.00" required/>
            </div>

            <div class="form_control">
                <label for="our_price">Our Price</label>
                <input type="number" step="0.01" id="our_price" name="our_price" value="0.00" required/>
            </div>
            <div class="form_control">
                <input type="submit" id="submit" value="Create Prize" />
            </div>
        </form>
    </body>
</html>