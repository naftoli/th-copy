<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'calendar.php';

//get parshos
$parshos = array();
$sql = "select * from parshos where start >= 2456530 and end <= 2456914";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $parshos[$row['name']]['start'] = $row['start'];
    $parshos[$row['name']]['end'] = $row['end'];
}

if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'Go' ) {
    echo "<pre>";
    //print_r( $_POST );
    echo "</pre>";
    //exit;

    $school = $_POST['school'];
    $class = isset( $_POST['class'] ) ? $_POST['class'] : 0;
    $user = isset( $_POST['user'] ) ? $_POST['user'] : 0;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    require 'class.tasksCustomizationNew.php';
    $tc = new TasksCustomizationNew;
    $campaigns = $tc->getCampaigns( $school );
    $enrolled = $tc->getCampaignsEnrolled( $school, $class, $user );
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="icalendar.js"></script>
        
        <link rel="stylesheet" href="customization.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/redmond/jquery-ui.css" />
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        
        <script type="text/javascript">         
            $( function() {             
                //variables to hold all 'unchecked' values
                var campaigns = [];
                var tasks = [];
                var missions = [];  

                //variables to hold all 'checked' values
                var campaignsAdded = [];
                var tasksAdded = [];
                var missionsAdded = [];         

                //function to use for updating any of the above arrays
                function updateArray( name, checked, val ) {    
                    if ( checked ) { 
                        var i = name.indexOf( val );
                        if ( i != -1 ) 
                            name.splice( i, 1 );
                    } else { 
                        var found = false;
                        for ( i = 0; i < name.length; i++ ) {
                            if ( name[i] == val ) {
                                found = true;
                                break;
                            }
                        }
                        if ( !found )
                            name.push( val );
                    }
                }
                
                <? if (!isset($_POST['submit'])) { ?>
                    $("#reset").hide();
                <? } ?>
                $("#resetFilter").click( function() {
                    window.location = 'task_customization.php';
                });
                
                $("#parsha").change( function() {
                    if ($(this).val() != 0) {
                        $(".dates").hide();
                        //get start end dates from val
                        var dates = $(this).val();
                        var pos = dates.indexOf(':');
                        var start = dates.substring(0, pos);
                        var end = dates.substring(pos+1);
                        $("#start_date").val(start);
                        $("#end_date").val(end);
                    } else {
                        $(".dates").show();
                        $("#start_date").val(2456530);
                        $("#end_date").val(2456914);
                    }
                });
                
                $("#school").change( function() { 
                    $("#class").empty();
                    $("#class").append("<option value='-1'>All</option>");
                    $("#user").empty();
                    $("#user").append("<option value='-1'>All</option>");
                    var id = $(this).val();
                    $.getJSON('ajax/getClasses.php', {id : id}, function( data ) {
                        $.each( data, function( key, val ) { 
                            var selected = '';
                            <? if ( isset( $_POST['class'] ) ) { ?>
                            if ( key == <?=$_POST['class']?> ) selected = "selected='selected'"; 
                            <? } ?>
                            var str = "<option value=" + key + ' ' + selected + ">" + val + "</option>";
                            $("#class").append( str );
                            if ( selected != '' )
                                $("#class").trigger('change');
                        });
                        <? if (!isset($_POST['submit'])) { ?>
                            $("#class").removeAttr('disabled');
                        <? } ?>
                        $("#class").next('div.newListSelected').remove();
                        $("#class").sSelect();
                        $("#user").next('div.newListSelected').remove();
                        $("#user").sSelect();
                    });
                });

                $("#class").change( function() {
                    $("#user").empty();
                    $("#user").append("<option value='-1'>All</option>");
                    var id = $(this).val();
                    $.getJSON('ajax/getUsers.php', {id : id}, function( data ) {
                        $.each( data, function( key, val ) { 
                            var selected = '';
                            <? if ( isset( $_POST['user'] ) ) { ?>
                            if ( key == <?=$_POST['user']?> ) selected = "selected='selected'"; 
                            <? } ?>
                            var str = "<option value=" + key + ' ' + selected + ">" + val + "</option>";
                            $("#user").append( str );
                        });
                        <? if (!isset($_POST['submit'])) { ?>
                            $("#user").removeAttr('disabled');                            
                        <? } ?>
                        $("#user").next('div.newListSelected').remove();
                        $("#user").sSelect();
                    });
                });

                $(".campaign").click( function(e) { 
                    //e.preventDefault();
                    var checked = $(this).is(":checked");
                    updateArray( campaigns, checked, $(this).val() );
                    updateArray( campaignsAdded, !checked, $(this).val() );

                    var o = this.parentNode; 
                    var task = $(o).next('.tasks').find('.task');
                    task.each( function() { 
                        var val = $(this).val();
                        task.attr('checked', checked); 
                        //get rid of tasks in tasks array b/c we already have it in campaign array
                        updateArray( tasks, true, val );
                        updateArray( tasksAdded, true, val );
                        var mission = task.parent().next('.missions').find('.mission');
                        mission.each( function() {
                            mission.attr('checked', checked);
                            //get rid of missions in missions array b/c we already have it in campaign array
                            updateArray( missions, true, val + ':' + $(this).val() );
                            updateArray( missionsAdded, true, val + ':' + $(this).val() );                          
                        });
                    });
                });
 
                $(".toggleTask").click( function(e) {
                    $("#taskInstructions").show();
                    $(this).parent().find(".campaign").show();    
                    $("#save").show();
                    $("#resetAll").show();
                    e.preventDefault();
                    var o = this.parentNode;
                    if ( !$(o).next('.tasks').html() ) { 
                        $(o).find('.icon').toggleClass('open');
                        var subject_id = $(o).find('.campaign').val();
                        var checked = $(o).find('.campaign').attr('checked');
                        <? if ( isset( $start_date ) ) { ?>
                            $(o).next('.tasks').append('<div class="loadingbar"><img src="images/loadingbar.gif" /></div>');
                            $.getJSON('ajax/getTasks.php', {
                                subject : subject_id, 
                                school: $('#school').val(), 
                                grade : $('#class').val(), 
                                user : $('#user').val(), 
                                start : $('#start_date').val(),  
                                end : $('#end_date').val()
                            }, function( data ) { 
                                var str = "<div class='listing'><li><span style='font-size:14px;'>Uncheck the tasks you don’t want to appear on your mission sheet.</span></li>";
                                $.each( data, function( cat, info ) {
                                    $.each( info, function( enrolled, names ) { 
                                        str += "<div class='listing'><li><input name='tasks[]' type='checkbox' class='task' value=\"" + cat + "\"";
                                        if ( enrolled && checked ) {
                                            str += ' checked ';
                                        }
                                        str += "><a href='#' class='toggleMission'><span class='icon'></span>" + cat + "</a>&nbsp;(Click on task if you would like to remove this task for a particular week)";
                                        str += "<div class='info tooltip' value=\"" + cat + "\">";
                                        
                                        var info = "";
                                        //info += '<strong>' + cat + ':</strong>';
                                        
                                        var json = [];
                                        var index = 0;
                                        var hasQty = false;
                                        
                                        if (cat == 'Learning with Parents') { 
                                                var s = "I learned with my parent/grandparent for my quota of minutes (It can be what you learned in school or something of your choice). How many minutes did you learn for?";
                                                info += "<br /><span style='font-weight:bold'>" + s + "</span>";
                                                info += '<br /><strong>Quotas:</strong>';
                                                info += "<br />My quota is ten minutes.<br />";
                                                info += "<span style='font-size:9px;'>Yeshiva Boys (Grade Pre 1a-Grade 1)<br />Yeshiva Girls (Grade Pre 1a-Grade 1)</span><br />"; 
                                                info += "<br />My quota is 15 minutes.<br />"; 
                                                info += "<span style='font-size:9px;'>Yeshiva Boys (Grade 2-Grade 3)<br />Yeshiva Girls (Grade 2-Grade 3)</span><br />";
                                                info += "<br />My quota is 20 Minutes.<br />";
                                                info += "<span style='font-size:9px;'>Yeshiva Boys (Grade 4-Grade 8)<br />Yeshiva Girls (Grade 4-Grade 8)</span><br />";
                                        
                                        } else {
                                        
                                            $.each( names, function( name, types ) { 
                                                var json = [];
                                                var index = 0;
                                                var hasQty = false;
                                                $.each( types, function( type, levels ) { 
                                                    json.push({
                                                        'type' : type, 
                                                        'levels' : [] 
                                                    });
                                                    $.each( levels, function( level, qty ) {
                                                        json[index].levels.push({
                                                            'level' : level, 
                                                            'qty'   : qty
                                                        });
                                                        if ( qty ) {
                                                            hasQty = true;
                                                        }
                                                    });
                                                    index++;
                                                });
                                                
                                                info += "<br /><span style='font-weight:bold'>" + name + "</span><span style='font-size:9px;'>";
                                                for ( var i = 0; i < json.length; i++ ) {
                                                    info += '<br />' + json[i].type;
                                                    if ( json[i].levels[0].level == json[i].levels[json[i].levels.length-1].level ) 
                                                        info += ' (' + json[i].levels[0].level + ')';
                                                    else 
                                                        info += ' (' + json[i].levels[0].level + '-' + json[i].levels[json[i].levels.length-1].level + ')';
                                                }
                                                info += "</span><br />";
                                                
                                                if ( hasQty && (cat != 'Tehillim') ) {
                                                    info += '<br /><strong>Quotas:</strong>';
                                                    for ( var i = 0; i < json.length; i++ ) {
                                                        info += '<br />' + json[i].type + '<br />';
                                                        for ( var j = 0; j < json[i].levels.length; j++ ) {
                                                            info += json[i].levels[j].level + ': <i>' + json[i].levels[j].qty + ' minutes</i><br />';
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                        str += info;
                                        //str += "&nbsp;[Task info]</a></li><div class='missions'></div></div>";
                                        str += "</div></li><div class='missions'></div></div>";
                                    });
                                });
                                str += "</div>";
                                $(o).next('.tasks').append( str );
                                            
                                //need to add it here b/c it can only work once the dom has dynamically loaded the tasks - it's not there before tis gets dynamically loaded
                                $(".toggleMission").click( function(e) { 
                                    $(".col_content").removeAttr('height');
                                    e.preventDefault();
                                    var o = this.parentNode;
                                    if ( !$(o).next('.missions').html() && !$(this).siblings(".loadingbar2").length ) { 
                                        $(o).find('.icon').toggleClass('open');
                                        var task = $(this).text();
                                        var checked = $(o).find('.task').attr('checked');
                                        <? if ( isset( $start_date ) ) { ?>
                                            $(this).parent().append("<div class='loadingbar2'><img src='images/loadingbar.gif' /></div>");
                                            $.getJSON('ajax/getMissions.php', {
                                                task : task, 
                                                school: $('#school').val(), 
                                                grade : $('#class').val(), 
                                                user : $('#user').val(), 
                                                start : $('#start_date').val(),  
                                                end : $('#end_date').val()
                                            }, function( data ) {
                                                var j = 0;
                                                var str = "<table>";
                                                $.each( data, function( i, v ) { 
                                                    if ( j == 0 ) {
                                                        str += "<tr>";
                                                    }
                                                    str += "<td><input name='missions[]' type='checkbox' class='mission' value=\"" + i + "\"";
                                                    if ( (v.enrolled == 1) && checked ) {
                                                        str += ' checked ';
                                                        //make sure task is also checked
                                                        $(o).find('.task').attr('checked', 'checked');
                                                    }
                                                    str += ">" + i;
                                                    if (v.mandatory == 1) 
                                                        str += " <span style='color:red;font-size:14px;'>*</span>";                                                    
                                                    str += "<br /><span style='font-size:9px;'>" + v.start + ' - ' + v.end + "</span>";
                                                    str += "</td>";
                                                    if ( ++j == 4 ) {
                                                        str += "<td><input type='button' class='checkAll' value='check all'>&nbsp;&nbsp;<input type='button' class='uncheckAll' value='uncheck all'></td></tr>";
                                                        j = 0;
                                                    }
                                                });
                                                if ( j != 4 && j != 0 ) { 
                                                    var showAll = true;
                                                    if (j == 1) 
                                                        showAll = false; 
                                                    while ( ++j != 5 ) {
                                                        str += "<td>&nbsp;</td>";
                                                    }
                                                    if (showAll) {
                                                        str += "<td><input type='button' class='checkAll' value='check all'>&nbsp;&nbsp;<input type='button' class='uncheckAll' value='uncheck all'></td></tr>";
                                                    } else {
                                                        str += "<td></td></tr>";
                                                    }
                                                }
                                                str += "</table>";
                                                $(o).next('.missions').append( str );
                                                $(".loadingbar2").hide();

                                                //need to add all click functions here b/c it doesn't see it until it's dynamically loaded
                                                $(".checkAll").click( function() { 
                                                    var checked = true;
                                                    var input = $(this).parent().parent().find('input[type=checkbox]');
                                                    input.attr('checked', checked);
                                                    input.each( function() {
                                                        var val = $(o).find('.task').val() + '~' + $(this).val();
                                                        updateArray( missions, checked, val );
                                                        updateArray( missionsAdded, !checked, val );
                                                    });
                                                });

                                                $(".uncheckAll").click( function() { 
                                                    var span = ($(this).parent().parent().children('td').find('span').text());
                                                    if (span.indexOf('*') != -1) {
                                                        alert("If you uncheck Mandatory Tasks your Chayolim will be unable to earn their medals, they will only progress in miles.");                                                        
                                                    }
                                                    var checked = false;
                                                    var input = $(this).parent().parent().find('input[type=checkbox]');
                                                    input.attr('checked', false);
                                                    input.each( function() {
                                                        var val = $(o).find('.task').val() + '~' + $(this).val();
                                                        updateArray( missions, checked, val );
                                                        updateArray( missionsAdded, !checked, val );
                                                    });
                                                });

                                                $(".mission").click( function(e) { 
                                                    //e.preventDefault();
                                                    var checked = $(this).is(":checked"); 
                                                    //if this is checked and it's mandatory show alert box
                                                    if ((!checked) && ($(this).next('span').text() == '*')) {
                                                        alert("If you uncheck Mandatory Tasks your Chayolim will be unable to earn their medals, they will only progress in miles.");
                                                    }
                                                    var val = $(o).find('.task').val() + '~' + $(this).val();
                                                    updateArray( missions, checked, val );
                                                    updateArray( missionsAdded, !checked, val );
                                                    //make sure that task is also checked if even one mission is checked
                                                    if ( checked ) { 
                                                        var task = $(o).find('.task');
                                                        var taskChecked = task.is(":checked");
                                                        if ( !taskChecked ) {
                                                            task.attr('checked', true);
                                                            updateArray( tasks, checked, task.val() );
                                                            updateArray( tasksAdded, !checked, task.val() );

                                                            var c = $(o).parent().parent().parent().find('.campaign');
                                                            var cChecked = c.is(":checked");
                                                            if ( !cChecked ) {
                                                                c.attr('checked', 'checked');
                                                                var campaign = $(o).parent().parent().parent().find('span').text();
                                                                updateArray( campaigns, checked, campaign );
                                                                updateArray( campaignsAdded, !checked, campaign );

                                                                //loop though tasks updating arrays
                                                                var task = $(this).parent().parent().siblings().find('.task');
                                                                $.each( task, function() { 
                                                                    var checked = $(this).is(":checked");
                                                                    updateArray( tasks, checked, $(this).val() );
                                                                    //updateArray( tasksAdded, !checked, $(this).val() ); causing bug
                                                                });
                                                            }

                                                            //loop through missions updating arrays
                                                            //get missions in current row
                                                            var mission = $(this).parent().siblings().find('.mission');
                                                            mission.each( function() { 
                                                                var val = $(o).find('.task').val() + '~' + $(this).val();
                                                                var mChecked = $(this).is(":checked");
                                                                updateArray( missions, mChecked, val );
                                                                //updateArray( missionsAdded, !mChecked, val ); causing bug
                                                            });
                                                            //get missions of other rows
                                                            var mission = $(this).parent().parent().siblings().find('.mission');
                                                            mission.each( function() { 
                                                                var val = $(o).find('.task').val() + '~' + $(this).val();
                                                                var mChecked = $(this).is(":checked");
                                                                updateArray( missions, mChecked, val );
                                                                //updateArray( missionsAdded, !mChecked, val ); causing bug
                                                            });
                                                        }
                                                    }
                                                });
                                            });
                                        <? } ?>
                                    } else {
                                        $(o).next('.missions').toggle();
                                        $(o).find('.icon').toggleClass('open');
                                    }
                                    return false;
                                });

                                $(".task").click( function(e) { 
                                    //e.preventDefault();
                                    var val = $(this).val();
                                    var checked = $(this).is(":checked");
                                    updateArray( tasks, checked, val );
                                    updateArray( tasksAdded, !checked, val );

                                    var o = this.parentNode;                                    
                                    //if checking the task, we need to also make sure the campaign gets checked
                                    if ( checked ) {
                                        var campaignChecked = $(o).parent().parent().parent().find('.campaign').is(":checked");
                                        if ( !campaignChecked ) {
                                            $(o).parent().parent().parent().find('.campaign').attr('checked', 'checked');
                                            var campaign = $(o).parent().parent().parent().find('span').text();
                                            updateArray( campaigns, checked, campaign );
                                            updateArray( campaignsAdded, !checked, campaign );

                                            //loop though tasks updating arrays
                                            var task = $(this).parent().siblings().find('.task');
                                            task.each( function() { 
                                                var checked = $(this).is(":checked");
                                                updateArray( tasks, checked, $(this).val() );
                                                //updateArray( tasksAdded, !checked, $(this).val() );
                                            });
                                        }
                                    }

                                    var mission = $(o).next('.missions').find('.mission');
                                    mission.each( function() {
                                        mission.attr('checked', checked); 
                                        //get rid of missions from missions array b/c we have it in tasks array
                                        updateArray( missions, true, val + '~' + $(this).val() );
                                        updateArray( missionsAdded, true, val + '~' + $(this).val() );
                                    });

                                });
                                $(".loadingbar").hide();
                            });
                        <? } ?>
                    } else {
                        $(o).next('.tasks').toggle();
                        $(o).find('.icon').toggleClass('open');
                    }
                    return false;
                });

                $('#save').click( function() { 
                    var save = true;
                    if ($("#class").val() == -1) {
                        save = confirm("WARNING! Saved changes will be applied to ALL grades and will override any changes that you made for an individual grade/class/ student.\nFIRST save changes that apply to your whole school then make changes to individual class/student.\nAre you sure you want to save?");
                    }
                    if (save) {
                        //get list of exceptions to delete
                        var exceptions = $(".deleteExceptions");
                        var len = exceptions.length;
                        var arr = [];
                        for (var i = 0; i < len; i++) {
                            if ($(exceptions[i]).is(":checked")) {
                                arr.push($(exceptions[i]).attr('name'));
                            }
                        }
                        $.post('ajax/customize.php', { 
                            campaigns : campaigns, 
                            tasks : tasks, 
                            missions : missions, 
                            campaignsAdded : campaignsAdded, 
                            tasksAdded : tasksAdded, 
                            missionsAdded : missionsAdded, 
                            school : $('#school').val(), 
                            grade : $('#class').val(), 
                            user : $('#user').val(), 
                            start : $('#start_date').val(),  
                            end : $('#end_date').val(), 
                            exceptions : arr 
                        }, function( data ) { 
                            //alert( data );
                            alert( "Saved." );
                            //history.go(0);
                            window.location = 'task_customization.php';
                        });
                    }
                });
                
                $("#submit").click( function() {
                    if ($(this).val() == 'Go') {
                        if ($("#school").val() == 0) {
                            alert("You must choose a school!");
                            return false;
                        }
                        else {
                            if ($("#class").val() == 0 || $("#user").val() == 0) {
                                alert("Please make class and student selection!");
                                return false;
                            }
                        }
                        if ( $("#start_date").val() > $("#end_date").val() ) {
                            alert("End date can not be before Start date!");
                            return false;
                        }
                    } else if ($(this).val() == 'Back') {
                        window.location = 'task_customization.php';
                    }
                });
                
                $("#resetAll").click( function() {
                    var action = confirm('Are you should you would like to assign all campaigns to all classes and students?');
                    if (action === true) {
                        $.post('ajax/resetCampaigns.php', {school_id : $("#school").val()}, function(data) {
                            alert(data);
                            window.location = 'task_customization.php';
                        });
                    }
                });
                
                if (document.getElementById('school')) {
                    $("#school").sSelect();
                    $("#class").addClass('disabled');
                }
                
                if ($("#class").val() == 0) {
                    $("#user").addClass('disabled');
                }
                
                $("#class").sSelect();
                $("#user").sSelect();
                $("#parsha").sSelect();
                
                $("a.prev").addClass('disabled');
                $("a.next").addClass('disabled');
                /*
                $("a.prev").click( function() {
                    $(this).siblings('select').find('option:selected').prev().attr('selected','selected').parent().change();
                });
                
                $("a.next").click( function() {
                    $(this).siblings('select').find('option:selected').next().attr('selected','selected').parent().change();
                });
                */
                <?
                if ( isset($_POST['school'] ) && $_POST['school'] != -1 ) {
                    echo "$('#school').trigger('change');";
                }
                ?>

            });
        </script>
    </head>

    <body>
        <? require 'admin_header.php'; ?>
        <h1>Personalize your Missions</h1>
        
        <?
        require_once 'class.adminSchools.php';
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        ?>
        <form method="post" action="task_customization.php">
            
            <div class="infobox2 marking_list clearfix" id="filter" style="padding: 10px">
                
                <span style="font-size:16px;">To personalize your mission sheets choose an option from all the fields below, then press go.</span><br />
                <br /><span style="font-size: 12px;">Please note: Whenever you make changes to the entire class or school it will override previous changes applied to
                individual classes or students. Therefore, make changes that apply to ALL grades BEFORE making changes that
                apply to individual classes or students.</span><br /><br />
                
                <? //if (count($schools) > 1) : ?>    
                <div class="school_list select_box">
                    <a class="prev button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Previous School')?></span>
                    </a>
                
                    <SELECT name="school" id="school">
                        <OPTION value="0">Please select a school</OPTION>
                        <? 
                        foreach ($schools as $id => $school) {
                            if (count($schools) == 1 || (isset($_POST['school']) && $id == $_POST['school'])) {
                                echo "<option value='$id' selected='selected'>$school</option>"; 
                            } else {
                                echo "<option value='$id'>$school</option>"; 
                            }               
                        }
                        ?>
                    </SELECT>               
                    
                    <a class="next button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Next School')?></span>
                    </a>                        
                </div>
                <? //endif; ?>
                
                <div class="class_list_div select_box">
                    <a class="prev button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Previous Class')?></span>
                    </a>
                
                    <SELECT name="class" id="class">
                        <OPTION value="0">Please select a class</OPTION>
                        <option value="-1">All</option>
                    </SELECT>               
                    
                    <a class="next button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Next Class')?></span>
                    </a>                        
                </div>
                
                <div class="user_list_div select_box">
                    <a class="prev button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Previous Student')?></span>
                    </a>
                
                    <SELECT name="user" id="user">
                        <OPTION value="0">Please select a student</OPTION>
                        <option value="-1">All</option>
                    </SELECT>               
                    
                    <a class="next button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Next Student')?></span>
                    </a>
                </div>
                
                <div class="date_list select_box">                  
                    <a class="prev button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Previous Week')?></span>
                    </a>
                
                    <SELECT name="parsha" id="parsha">
                        <option value="0">Please select Parsha or choose dates</option>
                        <?
                        foreach ($parshos as $parsha => $info) {
                            echo "<option value='" . $info['start'] . ':' . $info['end'] . "'";
                            if (isset($_POST['parsha']) && ($_POST['parsha'] == ($info['start'] . ':' . $info['end']))) {
                                echo " selected='selected' ";
                            }
                            echo ">" . $parsha . "</option>";
                        }
                        ?>
                    </SELECT>               
                    
                    <a class="next button">
                        <span class="icon"></span>
                        <span class="label"><?=T_('Next Week')?></span>
                    </a>
                </div>
                
                <div style="clear:both"></div>
                <div class="dates" style="width: 300px !important; margin-left: 29% !important">                  
                    <INPUT type="hidden" id="start_date" name="start_date" value="<?= isset($start_date) ? $start_date : 2456530?>">
                    <INPUT class="newListSelected" style="width: 150px !important;" type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew(isset($start_date) ? $start_date : 2456530))?>" onClick="getDate(this.form, 'start_date', true);"/>
                    <INPUT type="hidden" id="end_date" name="end_date" value="<?= isset($end_date) ? $end_date : 2456914?>">
                    <INPUT class="newListSelected" style="width: 150px !important" type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew(isset($end_date) ? $end_date : 2456914))?>" onClick="getDate(this.form, 'end_date', true);"/>
                </div>
                
                <div style="clear:both"></div>
                
                <div align="center">
                    <input type="submit" name="submit" id="submit" 
                    <? 
                    if (isset($_POST['submit']) && $_POST['submit'] == 'Go') 
                        echo "value='Back'";
                    else 
                        echo "value='Go'"; 
                    ?> />
                </div>
            </div>
        </form>
        
        <?
        if ( isset( $campaigns ) ) {
            ?>
            <script type="text/javascript">
                $("#filter").css('color', 'grey');
                $("#filter a").attr('disabled', true);
                $("#filter ul").attr('disabled', true);
                $("#filter select").attr('disabled', true);
            </script>
            <div class="module" style="padding: 10px" id="campaignInstructions">
                Click on the campaign(s) that you would like to personalize.
            </div>
            
            <div class="infobox2" style="padding: 10px; display: none" id="taskInstructions">
                <span style="font-size: 16px">
                    Click on the check box next to the campaign name to remove all tasks for this campaign.<br />
                    Click on the check box next to each task (in red) that tasks.<br />
                    Click on the the task (in red) to remove this task for a particular week.<br />
                    <br />
                </span>
                <span style="font-size: 12px">
                    Please Note:<br />
                    1. You must click SAVE to apply your changes.<br />
                    2. The Reset button will assign all campaigns to all classes and students.<br />
                    3. If you want to re-assign a campaign to all students (after individual classes or students have been removed from a
                    campaign) you must uncheck and RECHECK the campaign, then press save.
                </span>
            </div>
            
            <?
            echo "<div class='customize'>";
                echo "<ul class='campaigns'>";
                foreach ( $campaigns as $id => $campaign ) {
                    echo "<div class='campaignList'><span style='display:none'>$id</span>
                    <li><input type='checkbox' class='campaign' ";
                    if ( in_array($id, $enrolled) ) 
                            echo "checked";
                    echo " value='$id'> <a href='#' class='toggleTask'>" . $campaign . "<span class='icon'></span></a>";
                    echo "</li>";
                    echo "<ul class='tasks'>";                      
                    echo "</ul></div>";
                }
                echo "</ul>";
            echo "</div><br />";
            echo "<input type='submit' id='save' value='Save' />";
        } 
        if (isset($_POST['class']) && $_POST['class'] == -1) { ?>
            <input type="button" id="resetAll" value="Reset" />
        <? } ?>
        <script type="text/javascript">
            $(".campaign").hide();
            $("#save").hide();
            $("#resetAll").hide();
        </script>
    </body>
</html>