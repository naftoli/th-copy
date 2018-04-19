var findGetParameter; // requires findGetParameter to be loaded first...

$( document ).ready(function() {
    getLeaderBoard();
    // setup event listeners for when an option is changed
    $("input#location, input#gender, select#rank").change( getLeaderBoard );
    // parse the data and get the leaderboard
    function getLeaderBoard() {
        $("#leaderboard").html("<div class='loader'></div>"); // clear the leaderboard
        $("#user_position").text( "" );
        var postData = {
            location:   $("input#location:checked").val(),
            gender:     $("input#gender:checked").val(),
            rank:       $("select#rank").val(),
            user_id:    findGetParameter( "id" )
        };
        // fetch and render the leaderboard
        $.post( "/mobile/api/leaderboard.php", postData, renderLeaderBoard );
    }
    // render the leaderboard onto the page
    function renderLeaderBoard( response ) {
        var html = "";
        response.data.leaderboard.forEach( function( user, index ) {
            html += renderUser( user, index + 1, response.data.user_location );
        });
        $("#leaderboard").html( html );
        if ( response.data.user_location > 0 ){
            $("#user_position").html(
                "<div class='animated fadeIn'>" + 
                    "You are number " + formatNumber(response.data.user_location) + 
                    " out of " + formatNumber( response.data.total ) + 
                "</div>"
            );
        }
    }

    function renderUser ( user, position, current_user_position ) {
        var animated = position <= 26 ? "animated fadeIn" : "";
        var user_style = "";
        if ( current_user_position == position) {
            user_style = "background: #f9f9f9; color: #f7872a;"
        }

        var html = '<div class="user ' + animated + '" style="animation-delay: ' + (position / 10) + 's; ' + user_style + '">';
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
}); // end on page load anynomus function