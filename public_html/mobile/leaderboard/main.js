// make sure that we do not get a no-refrance error.
var authenticate;
// get the ID from get GET params
var id = findGetParameter("id");
// make sure we can authenticate the user
if ( !authenticate ) {
    window.location = "/mobile";
} else {
    authenticate( id ); // get the users ID and authenticate them
    setNavLinks ( id ); // set the user ID in the navigation links
    setUserPhoto( id ); // set the users profile picture
}

// setup all the links on the page
function setNavLinks( id ) {
    // links on the bottom of the page (nav-bar)
    if( localStorage.getItem("login") == "user" ){ // support the Kiosk Mode
        $( "#mainLink" ).attr('href', '/mobile/reg/medals/?id=' + id);
    };
    $("#missionsLink").attr('href', '/mobile/missionsNew.html?id=' + id);
    $("#rankLink").attr('href', 'rank.html?id=' + id);
    $("#storeLink").attr('href', '/mobile/store/index.html?id=' + id);
    // set the links on the top of the page
    $("#rankBoardLink").attr('href', 'rank.html?id=' + id);
    $("#medalBoardLink").attr('href', 'medals.html?id=' + id);
    $("#leaderBoardLink").attr('href', '/mobile/leaderboard/?id=' + id);
    // setup the ID in the bug reporting tool
    $(".bug-report img").data("user_id", id);
}

// set the user's photo on the top of the page
function setUserPhoto( id ){
    $.post('/mobile/reg/ajax/getPhoto.php', { user_id : id }, function( response ) {
        response = $.parseJSON( response );
        var html = '<a href="/mobile/reg/medals/index.html?id=' + id +'">';
        html += '<img id="userImg" src="'; // open the image tag
        if (response.mobile_pic) // up to date pictures
            html += '/mobile/reg/' + response.mobile_pic;
        else if (response.thumb) // if we only have a thumbnail
            html += '/thumbs/' + response.thumb;
        else if (response.photo) // picture is really old
            html += '/file_view.php?id=' + response.photo;
        html += '"></a>';
        $(".personalImg").append( html );
    });
}

// function to get GET paramaters
function findGetParameter(parameterName) {
    var result = null,
        tmp = [];
    var items = location.search.substr(1).split("&");
    for (var index = 0; index < items.length; index++) {
        tmp = items[index].split("=");
        if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    }
    return result;
}
