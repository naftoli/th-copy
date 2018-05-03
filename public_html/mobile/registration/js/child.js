// wrap all our logic inside childApp
var childApp = function(){
    // run these functions when we open the page
    loadSchools();
    
    // define the state
    var state = {
        current_page_id: "#"
    };

    // event listeners
    $( ".navigation-button" ).click( function( event ) {
        showPage( event.target.dataset.id );
    });

    $( "#school_id" ).change( function( event ) {
        loadClasses( event.target.value );
    });

    $( "#class_id" ).change( function( event ) {
        loadUsers( event.target.value );
    });

    // internal functions ( re-used or seperated code called above )
    function showPage( id ){
        $("#pages > section").hide();
        $("#pages > section#" + id).show();
    }

    function loadSchools() {
        $.get( "api/schools.php", function( response ) {
            renderDropdown( "school_id", response.data );
        });
    };

    function loadClasses( school_id ){
        $.get( "api/classes.php", { 'school_id': school_id }, function( response ) {
            renderDropdown( "class_id", response.data );
        });
    }

    function loadUsers( class_id ){
        $.get( "api/classes.php", { 'class_id': class_id }, function( response ) {
            // clean the data coming over the wire
            var names = [];
            response.data.users.forEach( function( user ){
                names.push({
                    "id": user.last,
                    "name": user.last
                });
            });
            
            renderDropdown( "last", names );
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

    // expose the following
    return {
        loadClasses: loadClasses
    }
}();