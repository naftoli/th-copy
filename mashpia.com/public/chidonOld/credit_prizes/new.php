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
    <title>New Chidon Credit Prize</title>
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
<h1>New Chidon Credit Prize</h1>
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
        <label for="credits_needed">Credits Needed</label>
        <input type="number" id="credits_needed" name="credits_needed" value="0" required/>
    </div>

    <div class="form_control">
        <input type="submit" id="submit" value="Create Prize" />
    </div>
</form>
</body>
</html>