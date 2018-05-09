/**
 * JS file for /mobile/registration/
 */
// run this code when loading script and expose functions under registrationApp
var registrationApp = function() {
    // default state of application
    var state = {
        users: [], // the users we are registering
        current_user_index: 0 // the current user we are confirming
    }
    // initialization functions
    $("button#start").click( renderStep1 );

    /******************** Rendering Functions ********************/
    function showPage( id ){
        $( "#pages section").hide();
        $( "#pages section#" + id ).show();
    }

    function renderStep1(){
        showPage("step-1");
        
        $.get( "api/users.php", handleAPIResponse( function( users ) {
            // throw out any registered users
            users.forEach( function( user ) {
                if( !user.user_registered ){
                    state.users.push( user );
                }
            });
            // skip to step 2
            if ( state.users.length == 1 && false ){
                renderStep2();
            } else {
                var html = "";
                state.users.forEach( function( user ){
                    html += '<div class="child col-6">' + 
                                '<img src="/mobile/reg/' + user.mobile_pic + '" />' +
                                '<p class="name">' + user.first + " " + user.last + '</p>' +
                                '<p class="reg_cost">' + user.reg_fee + '</p>' +
                            '</div>';
                });
                $("#step-1 .spinner").hide();
                $("#content").show();
                $("#step-1 #children").append( html );
            }
        }));
    }

    function renderStep2(){
        showPage("step-2");
    }
}();