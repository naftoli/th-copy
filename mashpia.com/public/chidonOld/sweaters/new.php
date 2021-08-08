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
        <title>New Chidon Sweater</title>
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
        <h1>New Chidon Sweater</h1>
        <p style="margin: 20px 10px">
            <a href="./index.php" class="button">Back to All Sweaters</a>
        </p>
        <form method="post" action="./create.php" enctype="multipart/form-data">

            <div class="form_control">
                <label for="sweater_name">Sweater Name</label>
                <input type="text" id="sweater_name" name="sweater_name" required/>
            </div>

            <div class="form_control">
                <label for="sweater_picture">Sweater Picture</label>
                <input type="file" id="sweater_picture" name="sweater_picture" />
            </div>

            <div class="form_control">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="0" required/>
            </div>

            <div class="form_control">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" required/>
            </div>

            <div class="form_control">
                <label for="gender">Gender:</label>
                <input type="radio" id="M" name="gender" value="M" required>
                <label for="M">Boys / Mens</label>
                <input type="radio" id="F" name="gender" value="F">
                <label for="F">Girls / Womens</label>
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
                <input type="submit" id="submit" value="Create Sweater" />
            </div>
        </form>
    </body>
</html>