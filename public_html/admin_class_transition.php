<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
$admin_auth = array('school');
require('header.php');

require_once( __DIR__ . "/classes/admin.php" );
require_once( __DIR__ . "/classes/school_class.php" );
$query = mysql_query(
    "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id']
);
$admin = new admin( mysql_fetch_assoc( $query ) );
$admin->get_schools();
foreach( $admin->schools as $school ){
    $school->get_classes();
}
?>
<!DOCTYPE html>
<html DIR="<?=$dir?>">
<head>
    <title><?=T_('Platoon Transition'), ' - ', T_('Tzivos Hashem Management System')?></title>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <link href="styles/admin/grey_select.css" rel="stylesheet" type="text/css">
    <link href="styles/admin/loader.css" rel="stylesheet" type="text/css">
    <style>
        table { width: 100% }
        th, td { padding: 4px 8px; border: 1px solid #888; }
        #step2 td:first-child { width: 50px; text-align: center; }
        #step3 td { /* width: 50%; */ text-align: center; padding: 15px 0px ; }
        #step4 { text-align: center; }
        small { font-size: .7em; }
    </style>
</head>
<body>
    <?php include_once('admin_header.php');?>
    <h1><?=T_('Platoon Transition')?></h1>
    <div class="infobox">
        <p><strong>Platoon Transition allows you to setup a large scale transition for multiple soldiers in your bases.</strong></p>
        <p>Select a platoon to see the current status of all soldiers in the platoon.</p>
        <p>Select one or more soldiers in a platoon and select one of the options in step 3 to set this change during the platoon transition process.</p>
        <p>Once you have finished configuring where you want all the soldiers to be moved to just press the "Make Live" button in step 4 to update all soldiers at once.</p>
    </div>

    <div id="step1">
        <h2>Step 1: Select Platoon</h2>
        <label for="platoon">Platoon:</label>
        <select id='platoon'>
            <?php foreach( $admin->schools as $school ) { ?>
                <optgroup label="<?=$school->school_name?>">
                    <option value='<?=$school->school_id?>-0'>No Platoon</option>
                <?php foreach( $school->classes as $platoon ) { ?>
                    <option value='<?=$school->school_id?>-<?=$platoon->class_id?>'><?=$platoon->name()?></option>
                <?php } ?>
                </optgroup>
            <?php } ?>
        </select>
    </div>

    <div id="step2">
        <h2>Step 2: Select Soldiers</h2>
        <div class="loader"></div>
        <table>
            <thead>
                <tr>
                    <th>Selected</th>
                    <th>Name</th>
                    <th>Moving To</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div id="step3">
        <h2>Step 3: Select Option For Selected Soldiers:</h2>
        <table>
            <tbody>
                <tr>
                    <td>
                        <label for="platoon-move">Select Platoon:</label>
                        <select id='platoon-move'>
                        <?php foreach( $admin->schools as $school ) { ?>
                            <optgroup label="<?=$school->school_name?>">
                            <?php foreach( $school->classes as $platoon ) { ?>
                                <option value='<?=$school->school_id?>-<?=$platoon->class_id?>'><?=$platoon->name()?></option>
                            <?php } ?>
                            </optgroup>
                        <?php } ?>
                        </select>
                        <a class="button" id="change-platoon">Move</a>
                    </td>
                    <td>
                        <a class="button" id="school-remove">
                            Remove from School.
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="step4">
        <h2>Step 4: Make updates Live</h2>
        <a class="button" id="make-live">Deploy Platoon Transition</a>
    </div>

    <script>
        var platoonTransitionApp = function(){
            // when the dropdown changes update the table
            $('select#platoon').change( getUsers );
            $('a#change-platoon').click( changePlatoon );
            $('a#school-remove').click( removeFromBase );
            $('a#make-live').click( transitionPlatoons );

            // update on page load
            $('select#platoon').change();

            // call getUsers action ( see api )
            function getUsers(){
                $('#step2 table').hide();
                $('#step2 .loader').show();

                var ids = $('select#platoon').val().split('-');
                var data = { school_id: ids[0] }
                if ( ids[1] !== '0' ) data.class_id = ids[1];

                $.post( '/api/core/platoon_transition.php?action=getUsers', data, function( response ) {
                    var users = response.data;
                    var no_users = '<tr><td colspan="3">No Soldiers In This Platoon</td></tr>'
                    var html = '';
                    users.forEach( function( user ) {
                        html += renderUserRow( user );
                    });
                    $('#step2 table tbody').html( html || no_users );
                    // dblclick trick
                    $('#step2 table input[type="checkbox"]').dblclick( function( event ){
                        $('#step2 table input[type="checkbox"]').attr('checked', false);
                        event.target.checked = true;
                    });
                    // show the UI
                    $('#step2 .loader').hide();
                    $('#step2 table').show();
                });
            }
            // call changePlatton action ( see api )
            function changePlatoon(){
                // let the user know that we got the button press
                $('a#change-platoon').text('Moving...');
                
                var selected_soldiers = getSelectedUserIds();
                var ids = $('select#platoon-move').val().split('-');
                var postData = { 
                    user_ids: selected_soldiers, 
                    school_id: ids[ 0 ], 
                    class_id: ids[ 1 ] 
                }
                $.post( '/api/core/platoon_transition.php?action=changePlatoon', postData, function( response ){
                    if ( response.success) {
                        getUsers();
                        // update the text on the button
                        $('a#change-platoon').text('Moved!');
                        setTimeout(function() {
                            $('a#change-platoon').text('Move');
                        }, 1000);
                    } else {
                        alert( response.error );
                    }
                });
            }
            // call removeFromBase action ( see api )
            function removeFromBase(){
                var selected_soldiers = getSelectedUserIds();
                $.post( '/api/core/platoon_transition.php?action=removeFromBase', {
                    user_ids: selected_soldiers
                }, function( response ){
                    if ( response.success) getUsers();
                    else alert( response.error );
                });
            }
            // call transitionPlatoons action ( see api )
            function transitionPlatoons(){
                if ( !confirm( "Are you sure you want to deploy this platoon transition? This action may not be able to be reversed." ) )
                    return false;
                
                $.get( '/api/core/platoon_transition.php?action=transitionPlatoons', function( response ){
                    if ( response.success ) {
                        alert( (response.data.rowCount / 2) + ' Soldiers Transitioned.');
                        getUsers();
                    } else alert( response.error );
                });
            }

            function getSelectedUserIds(){
                return $('#step2 table input[type="checkbox"]:checked').toArray().map(
                    function( soldier ){ return soldier.id }
                );
            }

            // renderer for the table
            function renderUserRow( user ) {
                var move_to = user.class_grade ? user.class_grade : '';
                move_to += user.class_sub ? ' - ' + user.class_sub : '';
                move_to += user.school_name && user.school_id !== user.current_school_id ? ' <small>(' + user.school_name + ')</small>' : '';
                move_to = !move_to && user.being_moved ? 'Being Removed From Base' : move_to;
                return '<tr>'
                    + '<td><input type="checkbox" id="' + user.user_id + '" ' + ( move_to ? '' : 'checked') + '/></td>'
                    + '<td><label for="' + user.user_id + '">' + user.user_serial + ': ' + user.first + ' ' + user.last + '</label></td>'
                    + '<td>' + ( move_to ? move_to : 'Not Being Moved' ) + '</td>'
                    + '</tr>'
            }
        }();
    </script>

    <? include('admin_footer.php'); ?>
</body>
</html>
