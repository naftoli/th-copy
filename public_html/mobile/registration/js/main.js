// https://tc39.github.io/ecma262/#sec-array.prototype.includes - https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/includes
Array.prototype.includes||Object.defineProperty(Array.prototype,"includes",{value:function(r,e){if(null==this)throw new TypeError('"this" is null or not defined');var t=Object(this),n=t.length>>>0;if(0===n)return!1;var i,o,a=0|e,u=Math.max(a>=0?a:n-Math.abs(a),0);for(;u<n;){if((i=t[u])===(o=r)||"number"==typeof i&&"number"==typeof o&&isNaN(i)&&isNaN(o))return!0;u++}return!1}});
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
    $("#payment-button").html('Pay And Register');
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