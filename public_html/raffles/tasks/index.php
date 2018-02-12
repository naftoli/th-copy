<?
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

$admin_auth = array('school'); 
require($_SERVER["DOCUMENT_ROOT"].'/header.php');

if($_POST['action'] == "run_raffle" && isset($_POST['raffle_id'])){
    if($debug) { echo "Location: run_raffle/run_raffle.php?raffle_id=".$_POST['raffle_id']."&save=".$_POST['save']; die(); }
    header("Location: run_raffle/run_raffle.php?raffle_id=".$_POST['raffle_id']."&save=".$_POST['save']);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffles: Manage Tasks</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            #content form{text-align: center;}
            select {
                background: url(/images/bg_smallButton.png) repeat-x scroll 0 0 #e6e5e5;
                border-color: #D3D3D3 #AAAAAA #888888;
                padding: 4px 8px;
                box-shadow: 0px 4px 10px #aaa;
                transition: box-shadow .1s linear;
                width: 49%;
            }
            select:hover{
                box-shadow: 0px 2px 8px #555;
                cursor: pointer;
            }
        </style>
    </HEAD>

    <BODY>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>Tzivos Hashem | Raffles: Manage Tasks</h1>
        
        <h2>Run Raffle.php</h2>
        <p>Please note that the script has a 10min timeout. Patience is required</p>
        <form method="POST">
            <input type="hidden" name="action" value="run_raffle"/>
            <span id="raffle_select_container"></span><br/><br/>
            <label for="save">Save raffle?</label><input type="checkbox" name="save"/><br/>
            <!--<input type="number" name="user_limit"/>--><br/>
            <input type="submit"/>
        </form>
        
        <script>
            var test_mode = <?=$_GET['test'] ? "true" : "false";?>;
            var debug_mode = <?=$_GET['debug'] ? "true" : "false";?>;
            
            $.post("/raffles/shared/ajax/list_raffles.php", {type: ""}, function(data){
                $("#raffle_select_container").html(data);
            });
        </script>
    </body>
</html>