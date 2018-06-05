// Array.prototype.find polyfill
Array.prototype.find=Array.prototype.find||function(r){if(null===this)throw new TypeError("Array.prototype.find called on null or undefined");if("function"!=typeof r)throw new TypeError("callback must be a function");for(var n=Object(this),t=n.length>>>0,o=arguments[1],e=0;e<t;e++){var f=n[e];if(r.call(o,f,e,n))return f}};
/**
 * JS file for /mobile/registration/
 */
// page setup
if ( !checkDateInput() ) { $('#dob, input[type="date"]').datepicker({ format: "yyyy-mm-dd" }); }
$("#successModal").on('hidden.bs.modal', function( event ) { window.location = "/mobile/reg/parent_detail.html" } );
$('[data-toggle="popover"]').popover();
hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places

var registrationApp = function() {
    var api_url = '/api/registration/user_registration.php'; // API endpoint for this page
    var state = {
        users: [], // the users we are registering
        selected_users: [], // users selected in step-1
        cart: [], // items that the user is paying for
        shipping_type: 1 // 1 or 2
    }
    // navigation buttons
    $(".start-step-1").click( step1 );
    $(".start-step-2").click( step2 );

    // form handlers
    $("#step-2 form").submit( confirmUser );

    step1();

    /*********************** CODE TO GUIDE USER THROUGH STEPS ***********************/
    // select users
    function step1() {
        showSection( "step-1" );
        toggleLoading( "step-1", true );
        state.users = [];

        getUsers().then( function( users ){ 
            if( users.length === 1 ) return step2();

            var html = '';
            state.users.forEach( function( user ) {
                html += templates.child( user );
            });
            $("#children").html( html );

            toggleLoading( "step-1", false );
        });
    }

    // confirm users
    function step2() {
        state.selected_users = [];
        // make sure that we have at least one user
        if ( state.users.length > 1 && $('#step-1 #children input:checked').length === 0 ) {
            return showError( 'Please select at least one child' );
        }
        // determine if the navigation should show
        if ( state.users.length == 1 ) {
            $('#step-2 .navigation').hide();
        } else {
            $('#step-2 .navigation').show();
        }
        // get only the users that they checked
        $.each( $('#step-1 #children input:checked'), function( index, checkbox ) {
            var user = Object.assign( { confirmed: false }, // set confirmed to false and get a new object
                state.users.find( function( user ){ // filter the user options
                    return user.user_id == checkbox.dataset.user_id // only return users with the correct ID
                })
            );
            state.selected_users.push( user );
        });
        // show the page
        templates.showUser( state.selected_users[0], 0 );
        showSection('step-2');
    }

    function step3() {
        
    }
    
    /*********************** FORM HANDLERS ***********************/
    function confirmUser( event ) {
        event.preventDefault();
        // update the user's information
        var postData = {};  var user_changed = false;
        var current_index = parseInt( $("#current_index").val() );
        var selected_user = state.selected_users[ current_index ];
        $( event.target ).serializeArray().forEach( function( item ) {
            if ( selected_user[ item.name ] !== undefined && 
                selected_user[ item.name ] != item.value 
            ) {
                user_changed = true;
                postData[ item.name ] = item.value;
            }
        });
        // if the user changed update him in the background...
        if( user_changed ){
            updateUser( selected_user.user_id, postData );
        }
        // TODO, detect and validate the charges accepted.
        // validate that they have accepted to be used in media campaigns
        if ( $(event.target).find( "#media" )[0].checked ){
            $(event.target).find( "#media" )[0].checked = false;
        } else {
            return showError( "You must indicate your acceptance of participation in Tzivos Hashem Media." )
        }
        current_index += 1;
        if ( state.selected_users.length <= current_index ){
            step3();
        } else {
            $("#current_index").val( current_index );
            templates.showUser( state.selected_users[ current_index ] );
        }
        debugger;
    }

    /*********************** HELPER FUNCTIONS ***********************/
    // navigate to a specific section
    function showSection( id ){
        $( "#pages section" ).hide();
        $( "#pages section#" + id ).show();
    }
    // toggle the loading dot
    function toggleLoading( id, loading ){
        id = "#" + id;
        if( !loading ) {
            $( id + " .spinner").hide();
            $( id + " .content").show();
        } else {
            $( id + " .spinner").show();
            $( id + " .content").hide();
        }
    }

    /*********************** API CALLS ***********************/
    function getUsers(){
        return new Promise( function( resolve, reject ){
            APIRequest( 'GET', api_url + '?action=getUsers', {}, function( response ) {
                response.users.forEach( function( user ) {
                    user.dob = user.dob.split("T")[0];
                    if ( user.registrationStatus.chayolei === false || user.registrationStatus.chidon === false )
                        state.users.push( user );
                });
                if ( state.users.length === 1 ) state.selected_users = state.users;
                resolve( state.users );
            });
        });
    }

    // TODO, update to point to unified core API
    function updateUser( user_id, postData ){
        return new Promise( function( resolve, reject ){
            $.post("api/users.php?user_id=" + user_id, postData, function( response ) {
                if ( !response.success ) {
                    showError( "There was an error while updating your child. Please try again later." );
                }
                resolve( response );
            });
        });
    }
}();

var templates = function(){
    return {
        child: function( child ){
            return '<div class="child col-12 col-lg-6" id="child-' + child.user_id + '"><label>' +
                '<div class="row">' +
                    '<div class="col-4">' +
                        '<img src="' + child.profilePicture + '" />' +
                    '</div><div class="col-6">' +
                        '<p class="name">' + child.first + " " + child.last + '</p>' +
                        ( child.registrationStatus.chayolei === false ? 
                            ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei + '</p>' ) : '' ) +
                        ( child.registrationStatus.chidon === false ? 
                            ( '<p class="reg_cost">Chidon: $' + child.registrationRates.chidon + '</p>' ) : '' ) +
                    '</div><div class="col-2">' +
                        '<input type="checkbox" data-user_id="' + child.user_id + '" />' +
                        '<span class="checkbox"></span>' +
                    '</div>' +
                '</div>' +
            '</label></div>';
        },
        showUser: function( user, index ){
            $( '#step-2 form #user_id' ).val( user.user_id );
            $( '#step-2 form #mobile_pic' ).val( user.mobile_pic );
            $( '#step-2 form #mobile_pic + img' ).attr( 'src', user.profilePicture );
            $( '#step-2 form #gender[value=\'' + user.gender + '\']')[0].checked = true;
            $( '#step-2 form #school_name' ).val( user.school.school_name );
            $( '#step-2 form #class_name' ).val( user.platton.class_grade + ' ' + user.platton.class_sub );

            $( '#step-2 form #current_index' ).val( index );
            
            $( '#step-2 form #first' ).val( user.first );       $( '#step-2 form #last' ).val( user.last );
            $( '#step-2 form #first_he' ).val( user.first_he ); $( '#step-2 form #last_he' ).val( user.last_he );
            $( '#step-2 form #lang_id' ).val( user.lang_id );   $( '#step-2 form #dob' ).val( user.dob.split("T")[0] );
        }
    }
}();