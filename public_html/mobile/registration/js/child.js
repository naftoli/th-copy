// wrap all our logic inside childApp
var childApp = function(){
    // run these functions when we open the page
    loadSchools();
    // start up the cropper image_upload library
    image_upload( {}, onImageUploaded );
    // use the hebrew keyboard on the given inputs
    hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places 
    
    // define the state
    var state = {
        preface: "th-"
    };

    /************************ EVENTS ************************/
    $( ".navigation-button" ).click( function( event ) {
        showPage( event.target.dataset.id );
    });
    // load the classes regardless of if they are in a base
    $( "#th-school_id, #no-th-school_id" ).change( function( event ) {
        loadClasses( event.target.value );
    });
    // load the last names if they are in a base
    $( "#th-class_id" ).change( function( event ) {
        loadUsers( event.target.value );
    });

    $( "#th-base-child" ).submit( registerChild );
    $( "#no-th-base-child" ).submit( createChild );

    /************************ PAGES ************************/
    // change the page (section)
    function showPage( id ){
        state.preface = id.replace("base", "");
        $("#pages > section").hide();
        $("#pages > section#" + id).show();

        if ( state.preface == "no-th-" && $("select#no-th-class_id option").length == 1 ){
            loadClasses( $("select#no-th-school_id").val() );
        }
    }
    // load list of schools
    function loadSchools() {
        $.get( "api/schools.php", function( response ) {
            renderDropdown( "school_id", response.data );
        });
    };
    // load the classes
    function loadClasses( school_id ){
        $.get( "api/classes.php", { 'school_id': school_id }, function( response ) {
            renderDropdown( "class_id", response.data );
        });
    }
    // load all last names
    function loadUsers( class_id ){
        $.get( "api/classes.php", { 'class_id': class_id }, function( response ) {
            // clean the data coming over the wire
            var names = [];
            response.data.users.forEach( function( user ){
                if ( names[ names.length - 1 ] != user.last ){
                    names.push( user.last );
                }
            });
            
            renderDropdown( "last", names.map( function( name ) {
                return { id: name, name: name }
            }));
        });
    }

    // update the information in the dropdowns based on a list
    function renderDropdown( id, options ) {
        // keep the disabled top option
        var html = $(" select#" + state.preface + id + " option:disabled").prop('outerHTML');
        
        options.forEach( function( option ) {
            html += "<option value='" + option.id + "'>" + option.name + "</option>";
        });
        // only update selects
        $( "select#" + state.preface + id ).html( html );
    }

    /************************ FORMS ************************/
    // convert the form to a JSON object
    function formToJSON( form ){
        var json = {};
        $(event.target).serializeArray().forEach( function( input ) {
            json[ input.name ] = input.value;
        })
        return json;
    }
    // handle what to do when an image is uploaded
    function onImageUploaded( data ){
        $("#user-img").attr("src", data.location );
        $("#mobile_pic").val( data.filename );
    }
    // register a existing child
    function registerChild( event ) {
        event.preventDefault();

        var postData = formToJSON( event.target );
        // validate the DOB is valid input
        if ( !postData.dob.match(/^\d{4}-(0[1-9]|1[0-2])-([0-3][0-9])$/) ){
            return showError( "Please enter a valid Date of Birth (YYYY-MM-DD)" );
        }

        $.post( "api/tasks/addChild.php", postData, function( response ){
            // show any API errors...
            if( !response.success ) return showError( response.error );
            // show the user if the fee is paid for
            if ( response.data.tuition ){
                $( "#tuition-paid" ).show();
                $( "#fee-not-paid" ).hide();
            } else {
                $( "#tuition-paid" ).hide();
                $( "#fee-not-paid" ).show();
            }

            $('#successModal').modal('show');
        });
    }
    // create a new child
    function createChild( event ) {
        event.preventDefault();

        var postData = formToJSON( event.target );
        
        if ( !postData.dob.match(/^\d{4}-(0[1-9]|1[0-2])-([0-3][0-9])$/) ){
            return showError( "Please enter a valid Date of Birth (YYYY-MM-DD)" );
        }

        if( !postData.gender ){
            return showError( "Please select your child's Gender." );
        }

        $.post("api/users.php", postData, function( response ){
            if( response.success ){
                $( "#tuition-paid" ).hide();
                $( "#fee-not-paid" ).show();         
                $( '#successModal' ).modal('show');
            } else {
                showError( response.error );
            }
        });
    }
}();