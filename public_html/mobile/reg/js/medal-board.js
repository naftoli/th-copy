/**
 * medal-board.js
 * 
 * creates global function medal_board( target ) that renders the medal board on a page
 * 
 * Requires jQuery, Medal.js (found in same folder)
 */

/**
 * @method medal_board renders the medal board
 * 
 * @param target target on the page to render the medal board into
 * @param user_id user_id to use in the request
 */
function medal_board( target, user_id ) {
    target = $( target ); // cast to jquery

    $.post( "/mobile/reg/medals/ajax/getMedalInfo.php", { user_id: user_id }, function( response ){
        try {
            response = JSON.parse( response ); // parse the response to JSON
            var html = '<div class="medal-board">';
            for ( var index = 0; index < response.length; index++ ){
                var medal = response[index];
                // create a new medal and render it on the page
                html += new Medal({
                    subject: medal.name,    url:    ("/mobile/reg/medals3.html?id=" + medal.id ),
                    picture: medal.photo ? ( "/file_view.php?id=" + medal.photo ) : "/kiosk/images/medals/holder.png",
                    animate: medal.photo ? true : false,    base_amount: medal.base_amount,
                    needed: medal.needed,   total:  medal.total,    next: medal.next
                }).render();
            }
            html += "</div>";
            target.html( html ); // render the HTML to the page
        } catch ( e ) { // catch any errors and let the user know
            console.error( e );
            target.html( '<div class="alert alert-danger">There was an error loading your medal board. Please email <a href="mailto:bugs@tzivoshashem.org">bugs@tzivoshashem.org</a> for further assistance</div>' );
        }

    }); // end POST request to get the data
}

/************************ Medal Object ************************/

/**
 * Medal( config )
 * 
 * config structure: {
 *      subject: The name of the medal subject
 *      picture: The URL to the picture of the current medal
 *      url: URL to redirect to when the medal is clicked
 *      needed: amount left to next level,
 *      total:  amount done
 *      next: next level
 * }
 */
function Medal( config ) {
    // set the attributes from the configuration
    this.subject = config.subject;  this.picture = config.picture;
    this.url = config.url;          this.needed = config.needed;
    this.total = config.total;      this.next = config.next;
    this.base_amount = config.base_amount; // the amount that we needed for the last medal
    this.animate = config.animate ? true : false;
    // colors for the medal to show
    this.colors = [
        "White", "Red", "Orange", "Yellow", "Green", 
        "Blue", "Purple", "Brown", "Gray", "Black", "Bronze"
    ]
}

Medal.prototype.getColor = function() {
    var index = this.next - 1;
    if ( index < this.colors.length ) {
        return this.colors[index];
    } else {
        return "Compleation";
    }
}

/**
 * .render()
 * 
 * No paramaters
 * 
 * returns the standard medal for the Medals based on the config passed to the constructor.
 */
Medal.prototype.render = function() {
    var status_width =  ( this.total - this.base_amount ) / ( this.needed - this.base_amount ) * 100;

    var html = "<div class='medal'>";
    html +=     '<a href="' + this.url + '">';
    html +=         '<img class="medal-img ' + ( this.animate ? "tada animated" : "" ) + '" src="' + this.picture + '" />';
    html +=     '</a>';
    html +=     '<div class="medal-subject">';
    html +=         '<span>' + this.subject + '</span>';
    html +=     '</div>';
    html +=     '<div class="medal-status progress">';
    html +=         '<div class="progress-bar ' + this.getColor( this.next - 1 ).toLowerCase() + '" role="progressbar" style="width: ' + status_width + '%;"></div>';

    if ( this.next - 1 < this.colors.length ) {
        html +=     '<span>' + ( this.needed - this.total ) + " to " + this.getColor( this.next - 1 ) + '</span>';
    } else {
        html +=     '<span>Campaign Compleate!</span>';
    }

    html +=     '</div>';
    html    += "</div>";
    
    return html;
}