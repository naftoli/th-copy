<?
// enable debuging
$debug = false;
if (isset( $_GET['debug'] ) ) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
}

$admin_auth = array('school');
require($_SERVER["DOCUMENT_ROOT"].'/header.php');

// load the required classes
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/class.schoolsUsers.php';

/***************** LOAD THE DATA **********************/
// get all the schools (should only return 1)
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Auction Winners</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            select {
                background: url(/images/bg_smallButton.png) repeat-x scroll 0 0 #e6e5e5;
                border-color: #D3D3D3 #AAAAAA #888888;
                padding: 4px 8px;
                box-shadow: 0px 4px 10px #aaa;
                transition: box-shadow .1s linear;
                min-width: 25%;
            }
            select:hover{
                box-shadow: 0px 2px 8px #555;
                cursor: pointer;
            }
            select:disabled {
                display: none;
                box-shadow: 0px 4px 10px #aaa;
                background: #e6e5e5;
                color: #888;
                cursor: initial;
            }
            #dropdowns{text-align: center;} 
            table {
                width: 100%;
                margin-top: 20px;
            }
            th, td {
                font-size: 12px;
                padding: 5px;
            }
            p#loader-status{
                text-align: center;
            }
        </style>
    </HEAD>

    <BODY>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
        <h1>End of year auction winners</h1>
        <p style="text-align: center;">
            <a class="button" id="export-to-csv">Export to CSV</a>
        </p>
        <h2></h2>

        <div id="dropdowns"> 

        <? if (count($schools) == 1) { ?>
            <select id="school_id" name="school_id" disabled>
                <option value="<?=array_keys($schools)[0];?>"><?=array_values($schools)[0];?></option>
            </select>
        <? } else { ?>
            <label for="school_id">Select school</label>
            <select id="school_id" name="school_id">
                <option value="">All Schools</option>
                <? foreach($schools as $school_id => $school_name){?>
                    <option value="<?=$school_id;?>"><?=$school_name;?></option>
                <?}?>
            </select>
        <? } ?>
        <? if ($admin_user['auth'] == 'super') {?>
            <label for="sorting">Sort by</label>
            <select id="sorting" name="sorting">
                <option value="school">School, Last, First</option>
                <option value="name">Last, First</option>
            </select>
        <? } ?>
        </div>


        <div id="winner_list_container"></div>

        <script>
            var test_mode = <?=isset($_GET['test']) ? "true" : "false";?>;
            var debug_mode = <?=isset($_GET['debug']) ? "true" : "false";?>;
        </script>
        <script src="/auction/winners/main.js"></script>
    </body>
</html>