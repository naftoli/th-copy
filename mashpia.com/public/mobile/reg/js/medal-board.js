
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
function medal_board(target, user_id, url) {
    target = $(target); // cast to jquery
    // set a default url
    if (url === undefined) {
        url = "/mobile/reg/medals3.html";
    };

    $.post("/mobile/reg/medals/ajax/getMedalInfo.php", { user_id: user_id }, function (response) {
        try {
            response = JSON.parse(response); // parse the response to JSON
            var html = '<div class="medal-board">';
            for (var index = 0; index < response.length; index++) {
                var medal = response[index];
                // create a new medal and render it on the page
                html += new Medal({
                    subject: medal.name, url: url ? (url + "?id=" + user_id + '&subject=' + medal.id) : "#",
                    picture: medal.photo ? ("/file_view.php?id=" + medal.photo) : "/mobile/reg/medals/images/Empty-Medal-Holder.png",
                    animate: medal.photo ? true : false, base_amount: medal.base_amount,
                    needed: medal.needed, total: medal.total, next: medal.next
                }).render();
            }
            html += "</div>";
            target.html(html); // render the HTML to the page
        } catch (e) { // catch any errors and let the user know
            console.error(e);
            target.html('<div class="alert alert-danger">There was an error loading your medal board. Please email <a href="mailto:bugs@tzivoshashem.org">bugs@tzivoshashem.org</a> for further assistance</div>');
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
function Medal(config) {
    // set the attributes from the configuration
    this.subject = config.subject; this.picture = config.picture;
    this.url = config.url; this.needed = config.needed;
    this.total = config.total; this.next = config.next;
    this.base_amount = config.base_amount; // the amount that we needed for the last medal
    this.animate = config.animate ? true : false;
    // colors for the medal to show
    this.colors = [
        "White", "Red", "Orange", "Yellow", "Green",
        "Blue", "Purple", "Brown", "Gray", "Black", "Bronze"
    ]

    if (localStorage.getItem("locallang") == "he") {

        this.colors = [
            "לבן", "אדום", "כתום", "צהוב", "ירוק",
            "כחול", "סגול", "חום", "אפור", "שחור", "ברונזה"
        ]

    }
}

Medal.prototype.getColor = function (current) {
    var CompleationText = "Compleation";
    if (localStorage.getItem("locallang") == "he") {
        CompleationText = "הסתיים";
    }

    var index = this.next - 1;
    if (current && index >= 1) {
        index -= 1;
    }
    if (index < this.colors.length) {
        return this.colors[index];
    } else {
        return CompleationText;
    }
}

/**
 * .render()
 * 
 * No paramaters
 * 
 * returns the standard medal for the Medals based on the config passed to the constructor.
 */

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

let campaigns = [];
var user_id = findGetParameter('id');
$.get('ajax/getMedalInfo.php', { user_id: user_id, subject_id: 5 }, setCampaigns);
function setCampaigns(data) {
    campaigns = JSON.parse(data);
    campaigns.forEach(campaign => {
        console.log(campaign)
    });
}

Medal.prototype.render = function () {
    //   var MissionsText = "Missions";
    var ToText = " To ";
    var CampaignCompleateText = "Campaign Compleate!";
    var progressbarFloat = "";
    var progressTextAlignment = "";


    if (localStorage.getItem("locallang") == "he") {

        ToText = " עד ";
        CampaignCompleateText = "הקמפיין הושלם!";

        //  MissionsText = "משימות";
        CampaignCompleateText = "הקמפיין הושלם!";
        progressbarFloat = "float: right;";
        progressTextAlignment = ' style="direction:rtl;" ';
    }

    if (this.total > 0)
        var status_width = (this.total) / (this.needed) * 100;
    else
        status_width = 0;

    var html = "<div class='medal'>";
    html += '<div class="medal-header">';
    html += '<a href="' + this.url + '">';
    html += '<img class="medal-img ' + (this.animate ? "bounceIn animated" : "") + '" src="' + this.picture + '" />';
    html += '</a>';
    html += '<div class="medal-header2">';
    html += '<div class="medal-header2-flex">';
    html += '<div class="medal-header2-details">';
    html += '<h2>' + this.subject + '</h2>';
    html += '<p><span>' + this.total + ' of ' + this.needed + '</span> monthly missions earned</p>';
    html += '</div>';
    html += "<div class='cornerLabel'>Don't miss a month to earn your medal by ב״א שבט</div>";
    html += '</div>';
    html += '<div class="medal-status progress">';
    html += '<div class="progress-bar ' + this.getColor(true).toLowerCase() + '" role="progressbar" style="width: ' + status_width + '%;' + progressbarFloat + '">'

    if (this.next - 1 < this.colors.length) {
        html += '<span ' + progressTextAlignment + '>' + (this.needed - this.total) + ToText + this.getColor(false) + '</span>';
    } else {
        html += '<span ' + progressTextAlignment + '>' + CampaignCompleateText + '</span>';
    }

    html += '</div>';
    html += '</div>';
    html += '</div>';
    html += '<div class="medal-footer"></div>';


    html += '</div>';
    html += "</div>";

    return html;


}