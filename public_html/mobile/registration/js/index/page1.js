var page1 = function(){

    function render( users ) {
        var html = "";
        users.forEach( function( user ){
            // render the template for each user
            html += 
            '<div class="child col-12 col-lg-6" id="child-' + user.user_id + '"><label>' +
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
        return html;
    }

    function getUsers( callback ) {
        $.get( "api/users.php", handleAPIResponse( function( users ) {
            callback( users );
        }));
    }

    return {
        render: render,
        getUsers: getUsers
    }
}();