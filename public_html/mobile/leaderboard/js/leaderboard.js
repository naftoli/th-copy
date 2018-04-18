var findGetParameter; // requires findGetParameter to be loaded first...

$( document ).ready(function() {
    getLeaderBoard();
    // setup event listeners for when an option is changed
    $("input#location, input#gender, select#rank").change( getLeaderBoard );
    // parse the data and get the leaderboard
    function getLeaderBoard() {
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
        console.log( "renderLeaderBoard => ", response );
        // TODO: Render LeaderBoard
    }
}); // end on page load anynomus function