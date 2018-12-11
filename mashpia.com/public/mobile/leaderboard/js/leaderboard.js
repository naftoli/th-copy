var findGetParameter; // requires findGetParameter to be loaded first...

var leaderboardApp = function(){
    // default internal state
    var state = {
        openedValues: {
            location: "army", 
            gender: "", 
            rank: ""
        },
        updateClicked: false
    }

    $( document ).ready(function() {
        getLeaderBoard( 0 );
        $( '#filtersModalContainer' ).on( 'hidden.bs.modal',  onModalClose );
        $( '#filtersModalContainer' ).on( 'show.bs.modal',    onModalOpen  );
        // setup event listeners for when an option is changed
        $("#update-leaderboard").click( function() {
            state.updateClicked = true; // do not update the UI
            getLeaderBoard( 0 ); // get a new leaderboard
            $('#filtersModalContainer').modal('hide');
        });
    }); // end on page load anynomus function
    /**
     * onModalOpen
     * 
     * update the values in the state when the modal is opened
     */
    function onModalOpen(){
        state.openedValues = {
            location: $("#location:checked").val(), 
            gender: $("#gender:checked").val(), 
            rank: $("#rank").val()
        }
    }
    /**
     * onModalClose
     * 
     * reset the modal to the position when opened if we are not saving anything
     */
    function onModalClose(){
        if ( state.updateClicked ){
            state.updateClicked = false;
        } else {
            $("#location[value='" + state.openedValues.location + "']").click();
            $("#gender[value='" + state.openedValues.gender + "']").click();
            $("#rank").val( state.openedValues.rank );
        }
    }

    // parse the data and get the leaderboard
    function getLeaderBoard( offset ) {
        if ( offset == 0 ) {
            $("#user_position, #leaderboard").html( "" );
        }
        // show the loading dots
        $("#leaderboard #load-more-box").remove();
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
        $.post( "api/leaderboard.php", postData, function( response ){
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
        } else {
            $("#user_position").html(
                "<div class='animated fadeIn'>" + 
                    formatNumber( data.total ) + " Soldiers!" + 
                "</div>"
            );
        }
        var html = "";
        if ( data.leaderboard.length == 0 ){
            html = '<div class="container">' +
                        '<div class="alert alert-branding">' + 
                            '<strong>No soldiers found.</strong><br/>Please adjust your filters.' +
                        '</div>' +
                    '</div>';
        } else {
            data.leaderboard.forEach( function( user, index ) {
                html += renderUser( 
                    user,   offset,
                    index + offset + 1,
                    data.user_location
                );
            });
        }
        
        $("#leaderboard").append( html );
    
        if ( 
            offset < 75 && 
            offset + data.leaderboard.length < data.total
        ) {
            $("#leaderboard").append(
                '<div id="load-more-box"><button class="btn btn-branding" id="load-more">Load More</button></div>'
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
}();
