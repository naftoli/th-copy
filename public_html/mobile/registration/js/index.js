/**
 * JS file for /mobile/registration/
 */
$("#successModal").on('hidden.bs.modal', function( event ) { window.location = "/mobile/reg/parent_detail.html" } );
$('[data-toggle="popover"]').popover()
// run this code when loading script and expose functions under registrationApp
var registrationApp = function() {
    // default state of application
    var state = {
        users: [], // the users we are registering
        selected_users: [], // users selected in step-1
        selected_user_index: 0, // the current user we are confirming
        shipping_charges: 0
    }
    // initialization functions
    hebrew_keyboard.attach( "#first_he, #last_he" ); // use hebrew in the right places
    image_upload( {}, onImageUploaded );
    $("button#start-step-1").click( renderStep1 );
    $("button#start-step-2").click( renderStep2 );
    $("button#start-step-3").click( renderStep3 );
    $("#step-2 form").submit( updateUser );
    $("#step-3 form").submit( updateShipping );
    $("#step-4 form").submit( page4.submit );

    /******************** Rendering Functions ********************/
    /**
     * showPage
     * 
     * change to the "page" with a given ID
     * 
     * @param { string/number } id
     */
    function showPage( id ){
        $( "#pages section").hide();
        $( "#pages section#" + id ).show();
    }

    /**
     * toggleLoading
     * 
     * toggle the spinner for a given step ( 1 or 3? )
     * @param {string/number} step 
     * @param {boolean} loading 
     */
    function toggleLoading( step, loading ){
        if( !loading ) {
            $("#step-" + step + " .spinner").hide();
            $("#step-" + step + " .content").show();
        } else {
            $("#step-" + step + " .spinner").show();
            $("#step-" + step + " .content").hide();
        }
    }

    /**
     * renderStep1
     * 
     * renders step 1 and updates state.users
     * pass true to refresh the page without redirecting
     * 
     * @param { boolean } update_only
     */
    function renderStep1( update_only ){
        if ( typeof update_only === "object" ) update_only = false;
        if ( !update_only ) {
            showPage("step-1");
            // if we already got the children. do not do it again.
            if ( $("#step-1 #children").html() != "" ) {
                return true;
            }
        }
        page1.getUsers( function( users ){
            state.users = [];
            users.forEach( function( user ) {
                if( !user.user_registered ) state.users.push( user );
            });
            if ( state.users.length == 1 ) {
                if ( !update_only ) renderStep2();
            } else {
                var html = page1.render( state.users );
                toggleLoading( 1, false );
                $("#step-1 #children").html( html );
            }
        });
    }

    /**
     * renderStep2
     * 
     * renders the step 2 page and updates state.selected_users
     */
    function renderStep2(){
        state.selected_users = []; // re-select the users
        state.selected_user_index = 0;
        // make sure that we have at least one user
        if ( state.users.length > 1 && $("#step-1 #children input:checked").length === 0 ) {
            return showError( "Please select at least one child" );
        }
        // if we only have one user he is selected by default
        if ( state.users.length == 1 ){
            var user = Object.assign( { confirmed: false }, state.users[0] );
            state.selected_users.push( user );
            $("#step-2 .navigation").hide();
        // add the selected users to state.selected_users
        } else {
            $("#step-2 .navigation").show();
            $.each( $("#step-1 #children input:checked"), function( index, checkbox ) {
                var user = Object.assign( { confirmed: false }, // set confirmed to false and get a new object
                    state.users.filter( function( user ){ // filter the user options
                        return user.user_id === checkbox.dataset.user_id // only return users with the correct ID
                    } )[0] // .filter returns an array. so get the first index
                )
                state.selected_users.push( user );
            });
        }
        showUser( state.selected_users[ state.selected_user_index ] );
        showPage("step-2");
    }

    /**
     * renderStep3
     * 
     * function to render the payments page
     */
    function renderStep3(){
        // setup page
        toggleLoading( 3, true );
        showPage("step-3");

        state.shipping_charges = 0;
        var school_ids = state.selected_users.map( function( user ) { return user.school_id } );

        // load shipping charges from API
        $.post( "api/tasks/shippingCost.php", { school_ids: school_ids }, function( response ){
            // if we have no shipping charges, then just skip to payments
            if ( !response.data ) return renderStep4();

            response.data.forEach( function(rate) { 
                $("#shipping-type-" + rate.type).text("$" + rate.rate) 
            });

            toggleLoading( 3, false );
        });
    }

    function renderStep4(){
        page4.render( state );
        showPage("step-4");
    }

    /**
     * showUser
     * 
     * show a user in the form on step 2
     * 
     * @param {object} user 
     */
    function showUser( user ){
        $( "#step-2 form #user_id" ).val( user.user_id );
        $( "#step-2 form #mobile_pic" ).val( user.mobile_pic );
        $( "#step-2 form #mobile_pic + img" ).attr( "src", user.profile_picture );
        $("#step-2 form #gender[value='" + user.gender + "']")[0].checked = true;

        $( "#step-2 form #first" ).val( user.first );       $( "#step-2 form #last" ).val( user.last );
        $( "#step-2 form #first_he" ).val( user.first_he ); $( "#step-2 form #last_he" ).val( user.last_he );
        $( "#step-2 form #lang_id" ).val( user.lang_id );   $( "#step-2 form #dob" ).val( user.dob )
    }

    /**
     * onImageUploaded
     * 
     * callback for when image is uploaded to server.
     * 
     * updates the UI and form ( steps 1 and 2 ) and attempts to update the users profile picture
     * 
     * @param {object} data 
     */
    function onImageUploaded( data ){
        var user_id = $( "#step-2 form #user_id" ).val();
        
        $("input#mobile_pic").val( data.filename );
        $("img#user-img, #child-" + user_id + " img").attr( "src", data.location );
        
        $.post("api/users.php?user_id=" + user_id, { mobile_pic: data.filename }, function( response ){
            if ( !response.success ){
                showError( "Could not update Profile Picture. We will try again when pressing Confirm.");
            } else {
                renderStep1( true );
            }
        });
    }

    /**
     * updateUser
     * 
     * event handler for step 2
     * 
     * @param {event} event 
     */
    function updateUser( event ){
        event.preventDefault();
        var postData = {};  var user_changed = false;
        var selected_user = state.selected_users[ state.selected_user_index ];
        selected_user.confirmed = true;
        // get all the items which changed
        $( event.target ).serializeArray().forEach( function( item ) {
            if ( selected_user[ item.name ] !== item.value ) {
                user_changed = true;
                postData = Object.assign( { [item.name]: item.value }, postData );
            }
        });
        // only post an update if we have information that has changed
        if ( user_changed ){
            $.post("api/users.php?user_id=" + selected_user.user_id, postData, function( response ) {
                if ( !response.success ) {
                    showError( "Could not update your child. We move on to the next one for now" );
                } else {
                    renderStep1( true );
                }
            });
        }
        // go to the next page if we are done
        if ( state.selected_users.length <= state.selected_user_index + 1 ){
            renderStep3();
        } else {
            state.selected_user_index += 1;
            showUser( state.selected_users[ state.selected_user_index ] );
        }
    }

    function updateShipping( event ){
        event.preventDefault();
        // update the shipping charges
        var selected_type = $("#shipping-type:checked").val();
        state.shipping_charges = parseInt(
            $("#shipping-type-"+selected_type).text().replace( /^\D+/g, '')
        );
        renderStep4();
    }
}();