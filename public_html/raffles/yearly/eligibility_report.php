<? // enable debuging
if ( isset( $_GET['debug'] ) ) {
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );
    $debug = true;
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require(dirname(__FILE__) . '/../../header.php');
/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

// load the schools
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Tzivos Hashem | Yearly Raffle Eligibility</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style type='text/css'>
        table {font-size: 12px; width: 100%;}
        tr {border-bottom: 1px solid #aaa;}
        th, td {padding: 3px 10px;white-space: nowrap;}
        select {
            background: url(/images/bg_smallButton.png) repeat-x scroll 0 0 #e6e5e5;
            border-color: #D3D3D3 #AAAAAA #888888;
            padding: 4px 8px;
            box-shadow: 0px 4px 10px #aaa;
            transition: box-shadow .1s linear;
        }
        select:hover{
            box-shadow: 0px 2px 8px #555;
            cursor: pointer;
        }
        select.hidden {
            display: none;
        }
        td:last-child, th:last-child {
            text-align: center;
        }
        div.dropdowns {
            text-align: center;
            margin-bottom: 10px;
        }
        div.dropdowns a {
            display: inline-block;
        }
        td.green{ color: green; }
        td.red{ color: red; }
    </style>
</head>
<body>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Yearly Raffle: Eligible Students</h1>

    <div class="dropdowns">
        <? if(count($schools) == 1) {?>
            <select id="school_id" name="school_id" class="hidden"  disabled>
                <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
            </select>
        <?} else {?>
            <i class="fa fa-university" aria-hidden="true"></i> School: 
            <select id="school_id" name="school_id">
                <option value="" selected disabled>Select a School</option>
                <? foreach($schools as $school_id => $school_name){?>
                    <option value="<?=$school_id?>"><?=$school_name?></option>
                <?}?>
            </select>
        <?}?>
    </div>
    <div class="dropdowns">
        <a class="button" id="generate"><i class="fa fa-refresh" aria-hidden="true"></i> Generate/Refresh Report</a>
    </div>

    <div id="eligible_table"></div>

    <script src="/raffles/yearly/js/eligibility_report.js"></script>
</body>
</html>