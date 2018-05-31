document.addEventListener("DOMContentLoaded", function(event) {
    var admin = Cookies.get('admin');
    var user = Cookies.get('user');

    if (!admin && !user) {
        $("#logged-out-footer").css({"display": "block"});
    } else {
        $("#logged-in-footer").css({"display": "block"});
    }

    if( user && localStorage.getItem( "login" ) == "user" ){
        $("#logged-in-footer #main-title").text( "My Account" );
        $("#logged-in-footer a").attr('href', "/mobile/reg/medals/?id=" + localStorage.getItem( 'id' ));
    }
    
    if (Cookies.get('shliach') && admin) {
        var shliach = Cookies.get('shliach');
        if (shliach == 1) {
            $("#logged-in-footer").css({"display": "none"});
            $("#my-shliach-footer").css({"display": "block"});
            //$(".bottomNav").empty();
            //$(".bottomNav").append( html );
        }
    } else if (admin) {
        $.post('ajax/checkMyShliach.php', { admin : admin }, function( isShliach ) {
            Cookies.set('shliach', isShliach ? 1 : 0);
            if (isShliach > 0) {
                $("#logged-in-footer").css({"display": "none"});
                $("#my-shliach-footer").css({"display": "block"});
            }
        });
    }
});

document.addEventListener("DOMContentLoaded", function(event) {
    var raffles = function(){

        var loading = false;

        function loadRaffle( raffle_id ){
            if ( !loading ){
                loading = true;
                $("#raffles").html( '<div class="loader"></div>' );
                $.get( "/mobile/news/api/raffle.php", { raffle_id: raffle_id }, renderRaffle);
            }
        }

        function renderRaffle( response ) {
            var data = response.data;
            var raffle = response.data.raffle;
            // update the state
            if ( data.previous ) {
                $("#raffle-navigation #previous").show();
                $("#raffle-navigation #previous a")
                    .text( data.previous.name )
                    .data("raffle_id", data.previous.raffle_id);
            } else {
                $("#raffle-navigation #previous").hide();
            }

            if ( data.next ) {
                $("#raffle-navigation #next").show();
                $("#raffle-navigation #next a")
                    .text( data.next.name )
                    .data("raffle_id", data.next.raffle_id);
            } else {
                $("#raffle-navigation #next").hide();
            }
            
            var html = "";
            html +=     '<div class="heDate">' + raffle.name + '</div>'
            html +=     '<div class="date bottom-border">' + raffle.hebrew_dates.from + " - " + raffle.hebrew_dates.to + '</div>';
            
            if ( raffle.type == "weekly" ){
                var prize = raffle.winner_info.boys[0] ? raffle.winner_info.boys[0] : raffle.winner_info.girls[0];
                // determine screen width
                var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
                var pic_src = w > 850 ? prize.prize_picture.full : prize.prize_picture.thumb ;
                // render special top bar for weekly raffles
                html += '<div class="weekly-raffle">';
                html +=     '<p>' + ( prize ? prize.prize_name : "Unknown Prize" ) + '</p>';
                html +=     '<div class="image" style="background-image: url(\'//mashpia.com/' + encodeURI(pic_src) + '\')" alt="picture of prize"></div>'
                html += '</div>';
                html += '<div class="bottom-border"></div>';
            }

            html +=     '<div class="mazalTov">!מזל טוב</div>';
            html +=     '<div class="winners">';
            html +=         '<div class="boys">';
            html +=             '<div class="b-t-title"><span>Boys</span></div>';

            for ( var index = 0; index < raffle.winner_info.boys.length; index++ ) {
                html += new winner_renderer( raffle.winner_info.boys[index], raffle.type != "weekly" ).render();
            }

            html +=         '</div>';
            html +=         '<div class="girls">';
            html +=             '<div class="b-t-title"><span>Girls</span></div>';

            for ( var index = 0; index < raffle.winner_info.girls.length; index++ ) {
                html += new winner_renderer( raffle.winner_info.girls[index], raffle.type != "weekly" ).render();
            }

            html +=         '</div>';
            html +=     '</div>';
            html +=     '<div class="footer-spacing"></div>';
            html += '</div>';

            $("#raffles").html( html );
            loading = false;
        }

        return {
            loadRaffle: loadRaffle
        }
    }();

    raffles.loadRaffle( 0 );
    $("#raffle-navigation a").click( function( event ) {
        if ( $( event.target ).data("raffle_id") )
            raffles.loadRaffle( $( event.target ).data("raffle_id") );
    })
});

/** object to render a winner */
var winner_renderer = function(winner, show_image){
    this.winner = winner;
    this.show_image = show_image;
};
winner_renderer.prototype.render = function(){
    var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    var pic_src = w > 850 ? this.winner.prize_picture.full : this.winner.prize_picture.thumb ;
    
    var html = '<div class="user winner">';
    if(this.show_image) { html += '<div class="image" style="background-image: url(' + pic_src + ')" alt="picture of prize"></div>'; }
    html += '<div class="b-t-description"> ';
    html += '<span><span class="rank">' + this.winner.rank + '</span><br />' + this.winner.name + '</span> ';
    html += '</div><div> <span class="school">' + this.winner.school + '</span><br/>';
    html += '<span class="grade">Grade: ' + this.winner.grade + '</span></div> ';
    html += '<div style="font-size: 11px; color: #3155a1"> ';
    if(this.show_image) {html += 'Prize: ' + this.winner.prize_name;}
    html += '</div></div><hr class="new-prize"/>';
    return html;
};