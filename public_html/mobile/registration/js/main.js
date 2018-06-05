// let the API know we are coming from the mobile site
$.ajaxSetup({
    beforeSend: function (xhr) {
       xhr.setRequestHeader("mobile","true");        
    }
});

/**
 * showError
 * 
 * Show an error message on the page
 * 
 * @param {string} message 
 */
function showError( message ){
    $("#errorModal .modal-body").text( message );
    $("#errorModal").modal('show');
    return false;
}

function handleAPIResponse( callback ){
    return function( response ){
        if ( !response.success ){
            showError( response.error );
        } else {
            callback( response.data );
        }
    }
}

// convert the form to a JSON object
function formToJSON( form ){
    var json = {};
    $(event.target).serializeArray().forEach( function( input ) {
        json[ input.name ] = input.value;
    })
    return json;
}

function checkDateInput() {
    var input = document.createElement('input');
    input.setAttribute('type','date');

    var notADateValue = 'not-a-date';
    input.setAttribute('value', notADateValue); 

    return (input.value !== notADateValue);
}

function APIRequest( type, url, data, callback ) {
    $.ajax({
        url: url,
        type: type,
        data: data,
        success: handleAPIResponse( callback ),
        error: function( xhr ) { 
            handleAPIResponse( callback )( xhr.responseJSON );
        }
    });
}