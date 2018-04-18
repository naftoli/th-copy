var findGetParameter; // requires findGetParameter to be loaded first...

$( document ).ready(function() {
    getLeaderBoard();
    // setup event listeners for when an option is changed
    $("input#location, input#gender, select#rank").change( getLeaderBoard );
    // parse the data and get the leaderboard
    function getLeaderBoard() {
        $("#leaderboard").html("<div class='loader'></div>"); // clear the leaderboard
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
        $("#leaderboard").html(""); // clear the leaderboard
        response.data.forEach( function( user, index ) {
            $("#leaderboard").append( renderUser( user, index + 1 ) );
        });
    }

    function renderUser ( user, position ) {
        var html = '<div class="user">';
        html +=     '<img src="/mobile/img_new/ranks/' + user.rank + '.svg" alt="' + user.rank + '" />';
        html +=     '<h1>#' + position + ': ' + user.first + ' ' + user.last + '</h1>'
        html +=     '<div class="medal_count">' + user.medal_count + ' Medals</div>'
        html +=     '<div class="mission_count">' + user.mission_count.replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' Missions</div>'
        html +=    '</div>';
        return html;
    }
}); // end on page load anynomus function