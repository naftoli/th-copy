// authenticate function, authenticates the user or redirects to the login page
function authenticate( user_id ) {
    // get the auth type
    var auth_type = localStorage.getItem("login");

    // child authentication
    if ( auth_type === "user" ) {
        var postData = {
            user_token: Cookies.get( 'user' ), // get the encrypted user token
            version : 2
        };

        $.post('/mobile/reg/ajax/checkAuth.php', postData, handleLoginResponse);
    // admin authentication
    } else {
        var postData = {
            admin_id: Cookies.get('admin'),
            user_id : user_id,
            version : 2
        };
        // make sure that the user's login is valid
        $.post('/mobile/reg/ajax/checkAuth.php', postData, handleLoginResponse);
    } // end admin authentication
}; // end authenticate function

/**
 * @method handleLoginResponse
 * 
 * @description Function to handle responses from checkAuth.php
 * 
 * @param {string} raw_response 
 */
function handleLoginResponse( raw_response ) {
    try {
        response = JSON.parse( raw_response );
        if ( !response.success ) {
            window.location = "/mobile";
        }
    } catch ( e ) { // if we have an invalid response from the server, just log out for now.
        window.location = "/mobile";
    }
};

// do some cleanup on page load
$( document ).ready( function() {
    if ( !Cookies.get('admin') ){
        window.location = "/mobile/reg/";
    }
    // get the authentication type
    var auth_type = localStorage.getItem("login");
    // default to admin
    if ( !auth_type ) {
        auth_type = "admin"; 
        localStorage.setItem( "login", "admin" ); // remember for next time
    }
    
    $("#logOff").click( function() {
        Cookies.remove('admin', { path: '/' }); // log off from the mobile site
        Cookies.remove('admin_id', { path: '/' }); // log off from the BC site
        Cookies.remove('user', { path: '/' }); // log off from the mobile site ( kiosk )
        localStorage.removeItem( "login" );// forget the login type

        if ( JSON.parse(localStorage.getItem( "kiosk" )) ) {
            localStorage.removeItem( "kiosk" );
            window.location = "/mobile/kiosk/";
        } else {
            window.location = "/mobile/reg/";
        }
    });
})