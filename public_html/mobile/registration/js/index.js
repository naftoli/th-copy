/**
 * JS file for /mobile/registration/
 */
// run this code when loading script and expose functions under registrationApp
var registrationApp = function() {
    // default state of application
    var state = {
        users: [],
        current_user_index: 0
    }
    // initialization functions
    loadUsers();

    function loadUsers(){
        $.get( "api/users.php", handleAPIResponse( function( users ) {
            // throw out any registered users
            users.forEach( function( user ) {
                if( !user.user_registered ){
                    state.users.push( user );
                }
            });
            // render the first step with the list of children
            renderStep1();
        }));
    }

    function renderStep1(){
        if ( state.users.length == 1 ){
            // go straight to step 2
        } else {
            // show a list of kids to pick from
        }
    }
}();