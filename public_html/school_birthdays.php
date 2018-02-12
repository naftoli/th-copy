<?php
$admin_auth = array('user');
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

//get dates
$dates = array();
$sql = "SELECT * FROM reports r 
		join parshos p on (r.start_date = p.start) 
        WHERE r.report_type='mission_cover_sheet' 
        AND r.visibility != 'none' 
        and p.year = 5776      
        ORDER BY p.start";   
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $dates[] = $row;
}
$today = unixtojd();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>School Birthdays</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            tr, th, td {
                padding: 10px;
                vertical-align: top;
            }
            .scrollable {
                height: 400px;
                overflow-x: scroll;
            }
            .scrollable .mosdos {
                width: 400px;
            }
            .scrollable .periods {
                width: 200px;
            }
        </style>
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $("#checkSchools").click( function() {
                    $(".school").attr("checked", true);      
                });
                
                $("#uncheckSchools").click( function() {
                    $(".school").attr("checked", false);
                });
                
                $("#checkParshas").click( function() {
                    $(".parsha").attr("checked", true);
                });
                
                $("#uncheckParshas").click( function() {
                    $(".parsha").attr("checked", false);
                });
                
                $('#get_birthdays').click(function(){
                    var schools = '';
                    $("#school_id input:checked").each(function(){
                        schools = schools + $(this).val() + ':';
                    });
                    schools = schools.substr(0, schools.length - 1)
                    $('#schools').val(schools);
                    
                    var parshas = '';
                    $("#parsha_period input:checked").each(function(){
                        parshas = parshas + $(this).val() + ':'
                    });
                    parshas = parshas.substr(0, parshas.length - 1);                
                    $('#parshas').val(parshas);
                    /*
                    var method = $(".method:checked").val();
                    $("#method").val( method );
                    */
                   	var gender = $(".gender");
                   	$.each(gender, function() {
                   		if ($(this).is(":checked")) {
                   			$("#gender").val($(this).val());
                   		}
                   	});
               
                    if (schools == '' || parshas == '' || $("#gender").val() == '')
                        alert('You must pick at least one school and one parsha and the gender.');
                    else
                        $('#get_birthday_form').submit();
                });
            });
        </script>
    </head>
    
    <body>
        <? require 'admin_header.php'; ?>        
        <h1>School Birthdays</h1>
        
        <form name="get_birthday_form" id="get_birthday_form" method="post" action="birthday_cert.php">
            <input type="hidden" name="schools" id="schools" />
            <input type="hidden" name="parshas" id="parshas" />
            <input type="hidden" name="gender" id="gender" />
        </form>
        
        <div class="body left marking_missions">
                    
            <div class="infobox2 marking_list clearfix">
        
                <div align="center">
                    <table>
                        <tr>
                            <th>Select Schools</th>
                            <th>Select Parshas</th>
                        </tr>
                        <tr>
                            <td id="school_id">
                                <div class="scrollable mosdos">
                                    <? 
                                    foreach ( $schools as $id => $school ) {
                                        //skip certain schools
                                        if ( in_array( $id, array(82,65,79,187,198,241) ) ) 
                                            continue;
                                        echo "<input type='checkbox' name='schools[]' value='" . $id . "' class='school'>" . $school . "<br />";
                                    } 
                                    ?>
                                </div>
                            </td>
                            <td id="parsha_period">
                                <div class="scrollable periods">
                                    <? 
                                    foreach ( $dates as $date ) { 
                                        echo "<input type='checkbox' name='parshas[]' value='" . 
                                            $date['end_date'] . "' class='parsha'>" . 
                                            $date['report_name'] . "<br />";
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                        	<td colspan="2" align="center">
                        		<input type="radio" class="gender" value="m" /> boys 
                        		<input type="radio" class="gender" value="f" /> girls
                        	</td>
                        </tr>
                        <tr>
                            <td align="center"><input type='button' id='checkSchools' value='Check All' />
                                <input type='button' id='uncheckSchools' value='Uncheck All' /></td>
                            <td align="center"><input type='button' id='checkParshas' value='Check All' />
                                <input type='button' id='uncheckParshas' value='Uncheck All' /></td>
                        </tr>
                        <tr>
                            <td colspan="2" align="center">
                                <input type="button" id="get_birthdays" value="Generate Birthday Certs">
                            </td>
                        </tr>
                    </table>
                </div>
                
            </div>
            
        </div>
    </body>    
</html>