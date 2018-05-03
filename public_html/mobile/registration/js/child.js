// wrap all our logic inside childApp
var childApp = function(){

    $( ".navigation-button" ).click( function( event ) {
        showPage( event.target.dataset.id );
    });

    function showPage( id ){
        $("#pages > section").hide();
        $("#pages > section#" + id).show();
    }

    function loadSchools() {
        $.get( "api/schools.php", function( response ) {
            renderDropdown( "school_id", response.data );
        });
    };
    loadSchools();

    function loadClasses( school_id ){
        $.get( "api/classes.php", { 'school_id': school_id }, function( response ) {
            renderDropdown( "class_id", response.data );
        });
    }

    function renderDropdown( id, options ) {
        // keep the disabled top option
        var html = $("select#" + id + " option:disabled").prop('outerHTML');
        
        options.forEach( function( option ) {
            html += "<option value='" + option.id + "'>" + option.name + "</option>";
        });
        // only update selects
        $( "select#" + id ).html( html );
    }

    return {
        loadClasses: loadClasses
    }
}();