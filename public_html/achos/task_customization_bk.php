<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'calendar.php';

if ( isset( $_POST['submit'] ) ) {
    echo "<pre>";
    //print_r( $_POST );
    echo "</pre>";

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

                $("#school").change( function() { 
                    $("#class").empty();
                    $("#user").empty();
                    $("#user").append("<option value='-1'>All</option>");
                    var id = $(this).val();
                    $.getJSON('ajax/getClasses.php', {id : id}, function( data ) {
                        $("#class").append("<option value='-1'>All</option>");
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
                    });
                });

                $("#class").change( function() {
                    $("#user").empty();
                    var id = $(this).val();
                    $.getJSON('ajax/getUsers.php', {id : id}, function( data ) {
                        $("#user").append("<option value='-1'>All</option>");
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
                                $.each( data, function( cat, info ) {
                                    $.each( info, function( enrolled, names ) { 
                                        var str = "<div class='listing'><li><input name='tasks[]' type='checkbox' class='task' value=\"" + cat + "\"";
                                        if ( enrolled && checked ) {
                                            str += ' checked ';
                                        }
                                        str += "><a href='#' class='toggleMission'><span class='icon'></span>" + cat + "</a>&nbsp;(click link to see missions)<a class='info tooltip'>";
                                        str += "<span><b></b>";
                                        str += '<strong>Tasks:</strong>';
                                        
                                        var json = [];
                                        var index = 0;
                                        var hasQty = false;
                                        
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
                                            
                                            for ( var i = 0; i < json.length; i++ ) {
                                                str += '<br />' + json[i].type;
                                                if ( json[i].levels[0].level == json[i].levels[json[i].levels.length-1].level ) 
                                                    str += ' (' + json[i].levels[0].level + ')';
                                                else 
                                                    str += ' (' + json[i].levels[0].level + '-' + json[i].levels[json[i].levels.length-1].level + ')';
                                            }
                                            str += '<br /><i>' + name + '</i><br />'; 
                                            
                                            if ( hasQty ) {
                                                str += '<br /><strong>Quotas:</strong>';
                                                for ( var i = 0; i < json.length; i++ ) {
                                                    str += '<br />' + json[i].type + '<br />';
                                                    for ( var j = 0; j < json[i].levels.length; j++ ) {
                                                        str += json[i].levels[j].level + ': <i>' + json[i].levels[j].qty + ' minutes</i><br />';
                                                    }
                                                }
                                            }
                                        });
                                        
                                        str += "</span>&nbsp;[Task info]</a></li><div class='missions'></div></div>";
                                        $(o).next('.tasks').append( str );
                                    });
                                });
                                
                                $("a.tooltip").hover( 
                                    function() {
                                        //var height = 1024;
                                        //var extra = $(this).find('span').height();
                                        //$(".col_content").height(height + extra);
                                    }, 
                                    function() {
                                        //$(".col_content").removeAttr('height');
                                    }
                                );
                                            
                                //need to add it here b/c it can only work once the dom has dynamically loaded the tasks - it's not there before tis gets dynamically loaded
                                $(".toggleMission").click( function(e) {
                                    $(".col_content").removeAttr('height');
                                    e.preventDefault();
                                    var o = this.parentNode;
                                    if ( !$(o).next('.missions').html() ) { 
                                        $(o).find('.icon').toggleClass('open');
                                        var task = $(this).text();
                                        var checked = $(o).find('.task').attr('checked');
                                        <? if ( isset( $start_date ) ) { ?>
                                            //$(this).parent().after('<div class="loadingbar2"><img src="images/loadingbar.gif" /></div>');
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
                                                    if ( v && checked ) {
                                                        str += ' checked ';
                                                        //make sure task is also checked
                                                        $(o).find('.task').attr('checked', 'checked');
                                                    }
                                                    str += ">" + i + "</td>";
                                                    if ( ++j == 4 ) {
                                                        str += "<td><input type='button' class='checkAll' value='check all'>&nbsp;&nbsp;<input type='button' class='uncheckAll' value='uncheck all'></td></tr>";
                                                        j = 0;
                                                    }
                                                });
                                                if ( j != 4 && j != 0 ) { 
                                                    while ( ++j != 5 ) {
                                                        str += "<td>&nbsp;</td>";
                                                    }
                                                    str += "<td><input type='button' class='checkAll' value='check all'>&nbsp;&nbsp;<input type='button' class='uncheckAll' value='uncheck all'></td></tr>";
                                                }
                                                str += "</table>";
                                                $(o).next('.missions').append( str );

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
                                            //$(".loadingbar2").hide();
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
                    if ($("#class").val() == -1) {
                        alert("WARNING! Saved changes will be applied to ALL grades and will override any changes that you made for an individual grade/class/ student.\nFIRST save changes that apply to your whole school then make changes to individual class/student.");
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
                        end : $('#end_date').val() 
                    }, function( data ) { 
                        //alert( data );
                        alert( "Saved." );
                        //history.go(0);
                        window.location = 'task_customization.php';
                    });
                });
                
                $("#submit").click( function() {
                    if ( $("#start_date").val() > $("#end_date").val() ) {
                        alert("End date can not be before Start date!");
                        return false;
                    }
                });
                
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
        <h1>Customize your tasks</h1>
            
        <form method="post" action="task_customization.php">
            <table id="filter">
                <tr>
                <?
                require_once 'class.adminSchools.php';
                $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
                $schools = $as->getSchools();
                if ( count( $schools ) > 1 ) {
                    echo "<td>School:</td>"; 
                    echo "<td><select name='school' id='school'>";
                    echo "<option value='-1'>All</option>";
                    foreach ( $schools as $id => $name ) {
                        echo "<option value=$id";
                        if ( isset( $_POST['school'] ) && $_POST['school'] == $id ) echo " selected";
                        echo ">$name</option>";
                    } 
                    echo "</select></td>";
                    echo "</tr><tr>";
                    echo "<td colspan='2' style='color:red; font-size:14px;'>Warning: Make changes that apply to ALL grades BEFORE making 
                            changes that apply to individual classes or students. You must click SAVE to apply your changes.</td>";
                    echo "</tr><tr>";
                    echo "<td>Class:</td>";
                    echo "<td><select name='class' disabled id='class'>";
                    echo "<option value='-1'>All</option>";
                    echo "</select></td>";
                } else if ( count( $schools ) == 1 ) {
                    $school_id = 0; 
                    echo "<td>School:</td>"; 
                    echo "<td><select name='school' disabled id='school'>";
                    foreach ( $schools as $id => $name ) {
                        echo "<option value=$id>$name</option>";
                        $school_id = $id;
                    } 
                    echo "</select></td>";
                    echo "</tr><tr>";
                    echo "<td>Class:</td>";
                    echo "<td><select name='class' id='class'>";
                    echo "<option value='-1'>All</option>";
                    require_once 'class.schoolClasses.php';
                    $sc = new SchoolClasses( $school_id );
                    $classes = $sc->getClasses();
                    foreach ( $classes as $class ) {
                        echo "<option value=" . $class['class_id'] . ">" . $class['class_grade'] . 
                                (empty( $class['class_sub'] ) ? '' : '-' . $class['class_sub']) . "</option>";
                    }                       
                    echo "</select></td>";
                    echo "<input type='hidden' name='school' value=" . $school_id . " />";
                }
                ?> 
                </tr>
                <tr>
                    <td>Child:</td>
                    <td>
                        <select name='user' disabled id='user'>
                            <option value='-1'>All</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <span class='dates'>
                        <INPUT type="hidden" id="start_date" name="start_date" value="<?= isset($start_date) ? $start_date : 2456530?>">
                        <LABEL>
                            From: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew(isset($start_date) ? $start_date : 2456530))?>" onClick="getDate(this.form, 'start_date', true);"/>
                        </LABEL>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <INPUT type="hidden" id="end_date" name="end_date" value="<?= isset($end_date) ? $end_date : 2456914?>">
                        <LABEL>
                            To: &nbsp;&nbsp;&nbsp;
                            <INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew(isset($end_date) ? $end_date : 2456914))?>" onClick="getDate(this.form, 'end_date', true);"/>
                        </LABEL>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" name="submit" id="submit" value="Submit" /></td>
                </tr>
            </table>
        </form>
        <div id="reset">
            <input type="button" id="resetFilter" value="Reset Filter" />
            <br />
        </div>
        <br />
        <?
        if ( isset( $campaigns ) ) {
            ?>
            <script type="text/javascript">
                $("#submit").hide();
                $("#filter").css('color', 'grey');
                $("#filter input").attr('disabled', true);
                $("#filter select").attr('disabled', true);                
            </script>
            <?
            echo "<div class='customize'>";
                echo "<ul class='campaigns'>";
                foreach ( $campaigns as $id => $campaign ) {
                    echo "<div class='campaignList'><span style='display:none'>$id</span>
                    <li><input type='checkbox' class='campaign' ";
                    if ( in_array($id, $enrolled) ) 
                            echo "checked";
                    echo " value='$id'> <a href='#' class='toggleTask'>" . $campaign . "<span class='icon'></span></a></li>";
                    echo "<ul class='tasks'>";                      
                    echo "</ul></div>";
                }
                echo "</ul>";
            echo "</div><br />";
            echo "<input type='submit' id='save' value='Save' />";
        }           
        ?>
    </body>
</html>