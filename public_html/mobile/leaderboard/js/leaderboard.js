var findGetParameter; // requires findGetParameter to be loaded first...

$( document ).ready(function() {
    getLeaderBoard( 0 );
    // setup event listeners for when an option is changed
    $("input#location, input#gender, select#rank").change( function() {
        getLeaderBoard( 0 ); // get a new leaderboard
    });
}); // end on page load anynomus function

// parse the data and get the leaderboard
function getLeaderBoard( offset ) {
    if ( offset == 0 ) {
        $("#user_position, #leaderboard").html( "" );
    }
    // show the loading dots
    $("#leaderboard #load-more").remove();
    $("#leaderboard").append("<div class='loader bottom'></div>");
    // setup the post request
    var postData = {
        location:   $("input#location:checked").val(),
        gender:     $("input#gender:checked").val(),
        rank:       $("select#rank").val(),
        user_id:    findGetParameter( "id" ),
        offset:     offset
    };
    // fetch and render the leaderboard
    $.post( "/mobile/api/leaderboard.php", postData, function( response ){
        renderLeaderBoard( response.data, offset );
    });
}
// render the leaderboard onto the page
function renderLeaderBoard( data, offset ) {
    $(".loader").remove();
    // render the user number
    if ( data.user_location > 0 ) {
        $("#user_position").html(
            "<div class='animated fadeIn'>" + 
                "You are number " + formatNumber(data.user_location) + 
                " out of " + formatNumber( data.total ) + 
            "</div>"
        );
    }
    var html = "";
    data.leaderboard.forEach( function( user, index ) {
        html += renderUser( 
            user,   offset,
            index + offset + 1,
            data.user_location
        );
    });
    $("#leaderboard").append( html );

    if ( 
        offset < 75 && 
        offset + data.leaderboard.length < data.total
    ) {
        $("#leaderboard").append(
            '<button class="btn btn-branding" id="load-more">Load More</button>'
        );
        $("#load-more").click( function() {
            getLeaderBoard( $(".user").length ); // load the next 25 users
        });
    }
}

function renderUser ( user, start, position, current_user_position ) {
    var user_style = "";
    if ( current_user_position == position) {
        user_style = "background: #f9f9f9; color: #f7872a;";
    }

    var html = '<div class="user animated fadeIn" style="animation-delay: ' + 
                    ( (position - start) / 10) + 's; ' + user_style + '">';
    html +=     '<img src="/mobile/img_new/ranks/' + user.rank + '.svg" alt="' + user.rank + '" />';
    html +=     '<div class="user_info">';
    html +=         '<h1 class="position">#' + position + '</h1>';
    html +=         '<h2 class="name">'+ user.first.toLowerCase() + ' ' + user.last.toLowerCase() + '</h2>'
    html +=         '<div class="medal_count">' + user.medal_count + ' Medals</div>'
    html +=         '<div class="mission_count">' + formatNumber(user.mission_count) + ' Missions</div>'
    html +=     '</div>';
    html +=    '</div>';
    return html;
}

function formatNumber( number ) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}