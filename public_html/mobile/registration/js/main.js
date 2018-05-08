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