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