/**
 * mark.js
 * 
 * This file is a startup and event handler script for mark.php
 * 
 * jQuery and functions.php are pre-loaded.
 */
$( '#oneCol' ).hide();

// * SETUP EVENT LISTENERS
$( '.checkboxDaily' ).click( onCheckboxClicked( true ) );
$( '.checkbox' ).not( '.textInput' ).click( onCheckboxClicked( false ) );
$( '.textInput input' ).keyup( onInputChanged );
$( '#checkAll' ).click( function(){ toggleAll( true ) } );
$( '#uncheckAll' ).click( function(){ toggleAll( false ) } );
$( '.dailyRow' ).click( toggleRow );

$( '#lookup' ).submit( lookupSoldier );
$( 'button#lookup-button' ).click( lookupSoldier );

$( '.dailyRow' ).click( toggleRow );
// always bring focus to the barcode/serial input
$( document ).click( function() { $('#lookup-user').focus() });

/**
 * Returns an event handler for the checkboxes on the page.
 * 
 * @param {boolean} daily 
 */
function onCheckboxClicked( daily ) {
    return function( event ) {
        var user_id = $('input#user_id').val();
        var div = event.target;
        // get the task_id and the date for the task
        var info = div.id.split(':');
        var task = info[0];
        var date = info[1];

        var mark_type = daily ? 'daily_task_mark' : 'task_mark';
        var url = '/delete_functions.php?function_name=delete_' + mark_type
        if ( $( div ).hasClass('unmarked') )
            var url = '/add_functions.php?function_name=add_' + mark_type

        var parameters = [ user_id, task, date ];
        url += '&parameters=' + parameters.join(',');
        
        $.getJSON( url, function( response ) {
            if ( response == false )
                return window.alert( 'Task not marked. Please try again' );
            $.post( '/ajax/updateMedalsRanks.php', { user : user_id } );
            update( div );
        });
    }
}

/**
 * Event handler for text inputs
 * 
 * @param {event} event 
 */
function onInputChanged( event ){
    var div = event.target;
    var user_id = $('input#user_id').val();

    var val = div.value || 0;
    var info = $( div ).parent()[0].id.split( ':' );
    var task = info[0];
    var date = info[1];

    var parameters = [user_id, task, date, val];
    url = "/add_functions.php?function_name=add_mark&parameters=" + parameters.join(',');
    
    $.getJSON( url, function( response ) {
        if (response != 1)
            return alert("Update not performed. Please try again.");

        $.post('/ajax/updateMedalsRanks.php', { user : user_id });
        update( div );
    });
}

/**
 * toggle all to be checked or unchecked
 * 
 * @param {boolean} event 
 */
function toggleAll( checked ){
    $("#loading").show();
    var user_id = $('input#user_id').val();
    var className = checked ? 'unmarked' : 'marked';

    var tasks = [];
    var dates = [];
    var boxes = $(".checkboxDaily, .checkbox");

    boxes.each( function( index, element ) {
        if ( $( element ).hasClass( className ) &&
            !$( element ).hasClass( 'textInput' )
        ) {
            var info = element.id.split(':');
            tasks.push( info[0] );
            dates.push( info[1] );
        }
    });

    tasks = tasks.join(':');
    dates = dates.join(':');

    var data = { action: checked ? 'add' : 'delete', data:  [ user_id, tasks, dates ] };
    $.post('/ajax/updateMarks.php', data, function( response ) {
        if (response.success == false) {
            alert( 'Update not performed.' );
            $( '#loading' ).hide();
        } else {
            $.post( '/ajax/updateMedalsRanks.php', { user : user_id } );
            boxes.each( function( index, element ) {
                if ( $(element).hasClass( className ) )
                    update( element );
            });
            $( '.dailyRow' ).attr( 'checked', !!checked );
            $( '#loading' ).hide();
        }
    });
}

/**
 * event handler for the checkbox on each daily row
 * 
 * @param {event} event 
 */
function toggleRow( event ) {
    var tasks = [];
    var dates = [];

    var div = event.target;
    var checked = div.checked;
    var user_id = $('input#user_id').val();
    var boxes = $( div ).parent().next( '.dailyBoxes' ).find( '.checkboxDaily' );

    boxes.each( function( index, element ) {
        if ( $( element ).hasClass( checked ? 'unmarked' : 'marked' ) ) {
            var info = element.id.split(':');
            tasks.push( info[0] );
            dates.push( info[1] );
        }
    });

    if ( tasks.length == 0 )
        return false;

    tasks = tasks.join(':');
    dates = dates.join(':');
    var data = { 
        action: checked ? 'add' : 'delete', 
        data: [ user_id, tasks, dates ]
    };

    $.post('/ajax/updateMarks.php', data, function( response ) {
        if ( response.success == false )
            return alert( 'Update not performed.' );
        $.post( '/ajax/updateMedalsRanks.php', { user : user_id } );
        boxes.each( function( index, element ) {
            if ( $( element ).hasClass( checked ? 'unmarked' : 'marked' ) )
                update( element );
        });
    });
}

/**
 * function update
 * 
 * updates the UI for the checkboxes to show if they are marked or not.
 * 
 * @param element div 
 */
function update( div ) {
    div = $( div );

    if ( div.hasClass('marked') ) {
        div.removeClass( 'marked' );
        div.addClass( 'unmarked' );
        if ( !div.hasClass( 'textInput' ) )
            div.empty();
    } else if ( div.hasClass('unmarked') ) {
        div.removeClass( 'unmarked' );
        div.addClass( 'marked' );
        if (!div.hasClass('textInput'))
            div.html( "<span class='checkmark'>&#10004;</span>" );
    }
}

function lookupSoldier( event ) {
    event.preventDefault();
    var serial = $("#lookup-user").val();
    if ( serial.match( '^7[0-9]{6}$' ) ) {
        $.ajax({
            type: 'POST',
            url: '/api/core/users?action=findSerial', 
            data: { serial: serial }, 
            success: function( response ) {
                if ( !response.success )
                    return alert('Could not find serial number');
                
                var user = response.data;
                $('#soldier').append('<option value="' + user.user_id + '">' + user.first + ' ' + user.last + '</option>')
                $('#soldier').val( user.user_id );
                $('#platoon').val( user.class_id );
                $('form#navigate').submit();
            },
            error: function( xhr, textStatus ) {
                return alert('Could not find serial number');
            }
        });
    } else if ( serial.match( '^3[0-9]{19}$' ) ) {
        $.ajax({
            type: 'POST',
            url: '/api/core/users?action=findBarcode', 
            data: { barcode: serial }, 
            success: function( response ) {
                if ( !response.success )
                    return alert('Could not find barcode');
                
                var user = response.data;
                $('#soldier').append('<option value="' + user.user_id + '">' + user.first + ' ' + user.last + '</option>')
                $('#soldier').val( user.user_id );
                $('#platoon').val( user.class_id );
                $('form#navigate').submit();
            },
            error: function( xhr, textStatus ) {
                return alert('Could not find barcode');
            }
        });
    } else
        alert('Invalid Serial Number (Must start with 7 and be 7 digits long)');
}

// footer is apparently not rendered.
$(".userMission").each( function() {			
    var user = $(this).attr('id');	
    var user_id = user.substring(user.indexOf('-') + 1);
    var image = 'All';
    var elem = this;

    $.get( 
        '/ajax/getMissionInfo.php',
        {user_id : user_id, type : image}, 
        function( data ) {
            data = $.parseJSON(data);
            
            var stickers = {
                1	: 'Shabbos Mevorchim Tehillim.gif', 
                4	: 'Tefillah.gif',
                12	: 'Mivtzoim.gif',
                13	: 'Niggunim.gif',
                16	: 'Sticker - Hiskashrus outline.png', 
                21	: 'sefer hamitzvos bw.png',
                27	: 'Tanya.gif',
                40	: 'Yomei Dipagra.gif',
                41	: 'Avos Ubonim.gif',
                42	: 'Vihalachta Bidrachov.gif',
                45	: 'Cheshbon Hanefesh.gif',
                90	: 'Chitas.gif',
                100	: 'Sticker - Brias Haguf_outline bw.png'
            }
            
            var str = "<div class='finalFooter'>";
            $.each(data, function(i, val) { 
                str += "<span class='footer_info'>";
                var j = 0;
                var s = stickers;
                $.each(val, function(indx, value) {
                    //build footer info
                    if (j++ == 0) { //first get sticker info
                        str += "<img src='/mission_report/stickerOutlines/" + s[i] + "' /><br /><b>" + indx + "</b><br />";
                    } else { //then get medal info
                        str += "<i>" + value + " to " + indx + "</i>";
                    }
                });
                str += "</span>"; 
            });
            str += "</div>";
            $(elem).find("#" + user_id).append(str);
        }
    );
});

$(".arrow-left").click( function() {
    var val = $( this ).parent().find( "option:selected" ).prev().val();
    var type = $(this).parent().find('select')[0].id;
    if ( val === undefined ) {
        alert( "There are no previous " + type + "s." );
        return;
    }
    $(this).parent().find('select').val( val );
    $(this).parent().find('select').change();

    if ( ['soldier', 'parsha'].includes( type ) )
        $('form#navigate').submit();
});

$(".arrow-right").click( function() {
    var val = $( this ).parent().find( "option:selected" ).next().val();
    var type = $(this).parent().find('select')[0].id;
    if ( val === undefined ) {
        alert( "There are no more " + type + "s." );
        return;
    }
    $(this).parent().find('select').val( val );
    $(this).parent().find('select').change();
    
    if ( ['soldier', 'parsha'].includes( type ) )
        $('form#navigate').submit();
});

$("select#platoon").change( function() {
    $.get('/ajax/getUsers.php', { id: $(this).val() }, function( data ) {
        if (data) {
            $("select#soldier").empty();
            var str = '';
            var users = $.parseJSON( data );
            for (key in users) {
                str += "<option value=" + key + ">" + users[key] + "</option>";
            }
            $("select#soldier").append( str );
        }
    });
});
