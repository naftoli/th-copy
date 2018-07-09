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
        move_to = !move_to && user.being_moved ? 'Transitioning From Base' : move_to;
        return '<tr>'
            + '<td><input type="checkbox" id="' + user.user_id + '" ' + ( move_to ? '' : 'checked') + '/></td>'
            + '<td><label for="' + user.user_id + '">' + user.user_serial + ': ' + user.first + ' ' + user.last + '</label></td>'
            + '<td>' + ( move_to ? move_to : 'Not Transitioning' ) + '</td>'
            + '</tr>'
    }
}();