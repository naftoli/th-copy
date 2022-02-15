<?
$admin_auth = array('school'); 
require('../../header.php');

if( isset($_GET['debug'])){
	//error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) : false;
if (!$id){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}
$sql = "SELECT * FROM chidon_sweaters WHERE sweater_id = '$id'";
$query = mysql_query($sql);
$sweater = mysql_fetch_assoc($query);

if (!$sweater){
    http_response_code(302);
    header('Location: ./index.php');
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Edit Chidon Sweater</title>
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
        <h1>Edit Chidon Sweater</h1>
        <p style="margin: 20px 10px">
            <a href="./index2.php" class="button">Back to All Sweaters</a>
        </p>
        <h2><?= $sweater['sweater_name'] ?></h2>
        <? if ($sweater['sweater_picture']){ ?>
            <div class="form_control">
                Current Picture: <br><img src="<?= $sweater['sweater_picture'] ?>" width="175" /><br><br>
            </div>
        <? } ?>
        <form method="post" action="./update.php" enctype="multipart/form-data">
            <input type="hidden" id="id" name="id" value="<?= $sweater['sweater_id']?>" required/>

            <div class="form_control">
                <label for="sweater_name">Sweater Name</label>
                <input type="text" id="sweater_name" name="sweater_name" value="<?= $sweater['sweater_name']?>" required/>
            </div>

            <div class="form_control">
                <label for="sweater_picture">Sweater Picture</label>
                <input type="file" id="sweater_picture" name="sweater_picture" />
            </div>

            <div class="form_control">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?= $sweater['quantity']?>" required/>
            </div>

            <div class="form_control">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" value="<?= $sweater['size']?>" required/>
            </div>

            <div class="form_control">
                <label for="gender">Gender:</label>
                <input type="radio" id="M" name="gender" value="M" <?= $sweater['gender'] === 'M' ? "checked" : "" ?> required>
                <label for="M">Boys / Mens</label>
                <input type="radio" id="F" name="gender" value="F" <?= $sweater['gender'] === 'F' ? "checked" : "" ?>>
                <label for="F">Girls / Womens</label>
            </div>

            <div class="form_control">
                <label for="price">Price</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= $sweater['price']?>" required/>
            </div>

            <div class="form_control">
                <label for="our_price">Our Price</label>
                <input type="number" step="0.01" id="our_price" name="our_price" value="<?= $sweater['our_price']?>" required/>
            </div>
            <div class="form_control">
                <input type="submit" id="submit" value="Save Changes" />
            </div>
        </form>
    </body>
</html>