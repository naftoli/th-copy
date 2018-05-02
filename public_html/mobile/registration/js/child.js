// wrap all our logic inside childApp
var childApp = function(){

    $( ".navigation-button" ).click( function( event ) {
        showPage( event.target.dataset.id );
    });

    function showPage( id ){
        $("#pages > section").hide();
        $("#pages > section#" + id).show();
    }

}();