/**
 * JS file for /mobile/registration/
 */
// run this code when loading script and expose functions under registrationApp
var registrationApp = function() {
    // default state of application
    var state = {
        users: [], // the users we are registering
        selected_users: [], // users selected in step-1
        current_user_index: 0 // the current user we are confirming
    }
    // initialization functions
    $("button#start-step-1").click( renderStep1 );
    $("button#start-step-2").click( renderStep2 );
    $("button#start-step-3").click( renderStep3 );

    /******************** Rendering Functions ********************/
    function showPage( id ){
        $( "#pages section").hide();
        $( "#pages section#" + id ).show();
    }

    function toggleLoading( step, loading ){
        if( !loading ) {
            $("#step-" + step + " .spinner").hide();
            $("#step-" + step + " .content").show();
        } else {
            $("#step-" + step + " .spinner").show();
            $("#step-" + step + " .content").hide();
        }
    }

    function renderStep1(){
        showPage("step-1");
        // if we already got the children. do not do it again.
        if ( $("#step-1 #children").html() != "" ) {
            return true;
        }
        // fetch the children and render the page.
        $.get( "api/users.php", handleAPIResponse( function( users ) {
            state.users = [];
            // throw out any registered users
            users.forEach( function( user ) {
                if( !user.user_registered ){
                    state.users.push( user );
                }
            });
            // skip to step 2
            if ( state.users.length == 1 ){
                renderStep2();
            } else {
                var html = "";
                state.users.forEach( function( user ){
                    html += 
                    '<div class="child col-12 col-lg-6"><label>' +
                        '<div class="row">' +
                            '<div class="col-4">' +
                                '<img src="' + user.profile_picture + '" />' +
                            '</div><div class="col-6">' +
                                '<p class="name">' + user.first + " " + user.last + '</p>' +
                                '<p class="reg_cost">$' + user.registration_fee + '</p>' +
                            '</div><div class="col-2">' +
                                '<input type="checkbox" data-user_id="' + user.user_id + '" />' +
                                '<span class="checkbox"></span>' +
                            '</div>' +
                        '</div>' +
                    '</label></div>';
                });
                toggleLoading( 1, false );
                $("#step-1 #children").html( html );
            }
        }));
    }

    function renderStep2(){
        state.selected_users = []; // re-select the users
        // make sure that we have at least one user
        if ( state.users.length > 1 && $("#step-1 #children input:checked").length === 0 ) {
            return showError( "Please select at least one child" );
        }
        // if we only have one user he is selected by default
        if ( state.users.length == 1 ){
            var user = Object.assign( { confirmed: false }, state.users[0] )
            state.selected_users.push( user );
        // add the selected users to state.selected_users
        } else {
            $.each( $("#step-1 #children input:checked"), function( index, checkbox ) {
                var user = Object.assign( { confirmed: false }, // set confirmed to false and get a new object
                    state.users.filter( function( user ){ // filter the user options
                        return user.user_id === checkbox.dataset.user_id // only return users with the correct ID
                    } )[0] // .filter returns an array. so get the first index
                )
                state.selected_users.push( user );
            });
        }
        console.log( state.selected_users );
        showPage("step-2");
    }

    function renderStep3(){
        showPage("step-3");
    }

    function showUser( user ){
        $( "#step-2 form #user_id" ).val( user.user_id );
        $( "#step-2 form #mobile_pic" ).val( user.profile_picture );
        $( "#step-2 form #mobile_pic + img" ).attr( "src", user.profile_picture );
        $("#step-2 form #gender[value='" + user.gender + "']")[0].checked = true;

        $( "#step-2 form #first" ).val( user.first );       $( "#step-2 form #last" ).val( user.last );
        $( "#step-2 form #first_he" ).val( user.first_he ); $( "#step-2 form #last_he" ).val( user.last_he );
        $( "#step-2 form #lang_id" ).val( user.lang_id );   $( "#step-2 form #dob" ).val( user.dob )
    }

    function updateUser(  ){

    }

    return {
        showUser: showUser
    }
}();