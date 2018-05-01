/**
 * @method medal_board renders the medal board
 * 
 * @param target target on the page to render the medal board into
 * @param user_id user_id to use in the request
 */
function rank_board( target, user_id, url ) {
    target = $( target ); // cast to jquery

    if ( url === undefined ) {
        url = "/mobile/reg/medals3.html";
    };

    $.post( "/mobile/reg/ajax/getRank.php", { user: user_id }, function( response ){
        try {
            response = JSON.parse( response ); // parse the response to JSON
            var html = '<div class="rank-board">';
            html += render_rank_board( response, user_id );
            html += "</div>";
            target.html( html ); // render the HTML to the page
        } catch ( e ) { // catch any errors and let the user know
            console.error( e );
            target.html( '<div class="alert alert-danger">There was an error loading your medal board. Please email <a href="mailto:bugs@tzivoshashem.org">bugs@tzivoshashem.org</a> for further assistance</div>' );
        }
    });

    function render_rank_board( data, user_id ) {
        var rank_index = 1;
        var html = "";
        for ( var i = 0; i < parseInt(data.medalsInfo[ parseInt(data.rank) + 1 ]); i++ ) {
            if ( i === parseInt( data.medalsInfo[ rank_index ] ) ) {
                html += rank_index > 1 ? "</div></div>" : ""; // close the last render_rank
                html += render_rank( rank_index, data.ranksPromoted[ rank_index ] );
                rank_index += 1;
            }
            if ( data.medals.length > i) {
                html += render_medal( data.medals[i], i + 1, user_id, url );
            } else {
                html += render_medal( {}, i + 1, user_id, false );
            }
            
        }
        return html;
    }

    
    function render_rank ( rank_ord, rank_promoted ) {
        var rank_names = [
            '', 'Private', 'Sergeant', 'Sergeant Major', 'Second Lieutenant', 'First Lieutenant', 'Captain', 
            'Major', 'Colonel', 'General', '1* General', '2* General', '3* General', '4* General', '5* General'
        ];

        var html = '<div id="rank-' + rank_ord + '">';
        html    +=  '<div class="rank-logo">';
        html    +=      '<div class="rank_name">' + rank_names[rank_ord] + '</div>';
        html    +=      '<div class="rank_promoted">(' + rank_promoted + ')</div>';
        html    +=      '<img src="/mobile/img_new/ranks/' + rank_ord + '.svg" alt="' + rank_names[rank_ord] + '"/>';
        html    +=  '</div><div class="rank-medals">';
        return html;
    }

    function render_medal ( medal, medal_number, user_id, url ) {
        var html = '<div class="rank-medal">';
        html    +=  '<span class="rank-medal-number">' + medal_number + '</span>';
        html    +=  '<a href="' + (url ? (url + "?id=" + user_id + '&subject=' + medal.subject ) : "#") + '">';
        if (medal.photo) 
            html    +=      '<img src="/file_view.php?id=' + medal.photo + '" />';
        else
            html    +=      '<img src="/kiosk/images/medals/holder.png" />';
        html    +=  '</a></div>';
        return html;
    }
}