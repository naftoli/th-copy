// hide everything from the global DOM
var registration_info = function() {
    let pass = '';
    while (pass !== 'zelda@5780') {
        pass = prompt("Please enter the password.", pass);
    }
    $("#report").show();

    // setup event listeners
    $('.base input, .base select, .base textarea').change( onChange );
    $('.base button.deactivate').click( deactivate );
    $('.base button.save').click( save );
    
    // show the save changes button once anything has been changed
    function onChange( event ) {
        var tr = $( event.target ).parent().parent();
        tr.find('.save').attr('disabled', false)
    }

    // deactivate the base, locking them out of their account.
    function deactivate( event ) {
        var tr = $( event.target ).parent().parent();
        var school_id = tr[0].dataset.school_id;
        var year = tr[0].dataset.year;
        // update the base
        updateBase( school_id, { school_era: year } )
            .then( function( response ) {
                if ( response.success )
                    event.target.parentElement.innerHTML = 'Inactive';
            });
    }

    // save changes
    function save( event ) {
        var tr = $( event.target ).parent().parent();
        var school_id = tr[0].dataset.school_id;
        const updates = tr.find('select, input').toArray()
            .reduce( function( obj, input ) {
                return Object.assign( {}, obj, { [input.name]: input.value } )
            }, {} );

        if ( updates.child_fee == '' ) {
            updates.child_fee = null;
        }

        // add registration notes
        updates.registration_notes = $("#notes").val()

        // update the base
        updateBase( school_id, updates )
            .then( function( response ) {
                if ( response.success ) {
                    event.target.disabled = true;
                    alert( 'Base Updated' );
                } else {
                    alert( response.message );
                }
            })
            .catch(error => console.log(error));
    }

    function updateBase( school_id, updates ) {
        return new Promise( function( resolve, reject ){
            $.ajax({
                url: '/api/core/bases?id=' + school_id,
                type: 'POST',
                data: JSON.stringify( updates ),
                error: reject,
                dataType:"json",
                success: resolve,
                contentType:"application/json; charset=utf-8",
            });
        });
    }

}();
