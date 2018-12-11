<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
    echo "<pre>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
if ($admin_user['auth'] != 'super') {
    header("Location: /reports/");
}
/***************** IMPORTS **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
// load up the mishna Info
require_once $_SERVER["DOCUMENT_ROOT"].'/class.mishnaInfo.php';

$he_chars = array(
	1	=>	'א',    2	=>	'ב',    3	=>	'ג',    4	=>	'ד',    5	=>	'ה',
	6	=>	'ו',    7	=>	'ז',    8	=>	'ח',    9	=>	'ט',    10	=>	'י',
	11	=>	'יא',   12	=>	'יב',   13	=>	'יג',   14	=>	'יד',   15	=>	'טו',
	16	=>	'טז',   17	=>	'יז',   18	=>	'יח',   19	=>	'יט',   20	=>	'כ',
	21	=>	'כא',   22	=>	'כב',   23	=>	'כג',   24	=>	'כד',   25	=>	'כה',
    26	=>	'כו',   27	=>	'כז',   28	=>	'כח',   29	=>	'כט',   30	=>	'ל'
);

if ($debug) echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Mishna Reports | Edit Mishnos</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css"/>
        <link href="/raffles/shared/styles/shipping/grey_slider.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <style>
            table{width: 100%;}
            tr {border-bottom: 1px solid #888;}
            td, th{min-width: 0px; text-align: center; padding: 4px 8px;}
            .options{text-align: center;}
            input.mishna_lines {width: 90px;text-align: center;background: no-repeat;border: none;border-bottom: 1px solid;font-size: 1.1em;margin-right: 15px;}
        </style>
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Edit Mishnos</h1>
        <div class="options">
            <div class="row">
                <i class="fa fa-university" aria-hidden="true"></i> Mesechto: 
                <select id="mesechto" name="mesechto"></select>
            </div>
        </div>
        
        <div id="perakim"></div>
        
        <script>
            $(document).ready(function(){
                // load up the mesechtos from the DBS...
                getMesechtos();
                var he = <?=json_encode( $he_chars )?>;
                
                $("#mesechto").change(getPerokim);
                
                function getMesechtos(){
                    $.post('/ajax/getAllMishnos.php', function( success ) {
                        var mesechtos = $.parseJSON( success );
                        $("#mesechto").empty();
                        $("#perokim").empty();
                        var html = '';
                        for (var id in mesechtos) {
                            html += "<option value='" + id + "'>" + mesechtos[id] + "</option>";
                        }
                        $("#mesechto").append( html );
                        getPerokim();
                    });
                }
                
                function getPerokim(){
                    var mesechto = $("#mesechto").val();
                    $.post("/ajax/getPerokim.php", {mesechto: mesechto}, function(perokim){
                        perokim = JSON.parse(perokim);
                        var html = "";
                        
                        for(var perek in perokim) {
                            html += "<h2> פרק " + he[perek] + " </h2>";
                            // render the table header...
                            html += "<table>";
                            html +=     "<thead>";
                            html +=         "<th>Mishna</th><th># Lines</th>";
                            html +=     "</thead>";
                            // render the body
                            html +=     "<tbody>";
                            
                            for(var mishna in perokim[perek]){
                                var lines =  perokim[perek][mishna];
                                html += "<tr>";
                                html += "<td>" + he[mishna] + "</td>";
                                html += "<td>";
                                html +=     "<input type='text' class='mishna_lines' data-mishna='" + mishna;
                                html +=         "' data-perek='"+perek+"' data-mesechto='" + mesechto + "' value='" + lines + "'/>";
                                html +=     "<button class='mishna_lines_save'>Update</button>";
                                html += "</td>";
                                html += "</tr>";
                            }
                            // render the end of the table
                            html +=     "</tbody>";
                            html += "</table>";
                        }
                        
                        $("#perakim").html(html);
                        
                        $(".mishna_lines_save").click(function(event){
                            var input = $(event.target).siblings();
                            var data = Object.assign({}, input[0].dataset);
                            data.lines = input.val();
                            
                            $.post("ajax/edit_mishna.php", data, function(response){
                                response = JSON.parse(response);
                                if(!response.success){
                                    alert(response.error);
                                }
                            });
                        });
                    });
                }
            });
            
        </script>
    </body>
</html>