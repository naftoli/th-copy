// set up on page load
$(document).ready(function(){
    $("select#school_id").change(reloadRaffleWinners); // set up the initial handler on page load
    $("select#sorting").change(reloadRaffleWinners);
    $("a#export-to-csv").click(exportToCSV);
    //$("select#school_id").change(); // auto run the script for the first school?
    reloadRaffleWinners(); // load the raffles dropdown box
});


function reloadRaffleWinners() {
    
    $('div#winner_list_container').html("<div class='loader'></div><p id='loader-status'>Getting winners....</p>"); // show the loader
    
    var school_id = $("select#school_id").val();
    var auction_id = 82 //$("select#auction_id").val();
    var sorting = $("select#sorting").val();
    var single_school = $("select#school_id")[0].disabled;
    
    $.post("/auction/winners/ajax_get_auction_winners.php", {school_id: school_id, auction_id: auction_id, single_list: true, sorting: sorting}, function(data){
        $("p#loader-status").text("Parsing Data...."); // update the status on slow devices
        auction_winners = JSON.parse( data ); // get the raffle info
        
        // if the data is undefined (no raffle selected)
        if (auction_winners[0] === undefined) {
            return $('div#winner_list_container').html("<h3 style='margin-top: 15px; text-align: center'>Sorry, there are no auction winners for your school</h3>");
        }
        $("p#loader-status").text("Generating Table...."); // update the status on slow devices
        
        var html = '<table id="winners-table">';
        html += '<thead>';
        if ( !single_school ) {
            html += '<th>School</th>';
        }
        html += '<th>Grade</th> <th>Last Name</th> <th>First Name</th> <th>Address</th> <th>City</th> <th>State</th> <th>Zip</th>';
        if (school_id == 269 || school_id == 61 ) {
            html += '<th>Country</th>';
        }
        html += '<th>Prize Won</th></thead>';
        html += '<tbody>';
        
        auction_winners.forEach( function ( auction_winner ){
            html += new winner_renderer( auction_winner ).render(
                ( school_id == 269 || school_id == 61 ), 
                !single_school
            );
        });
        
        html += '</tbody></table>';
        
        $('div#winner_list_container').html(html);
    });
}

/*********************** RENDERING OBJECT *****************************/
function winner_renderer(winner) {
    this.winner = winner;
}

winner_renderer.prototype.render = function( show_country, show_school){
    // render the table row
    this.html = '<tr class="winner">';
    if ( show_school ) {
        this.html += '<td>' + (this.winner.hachayol_name ? this.winner.hachayol_name : this.winner.school_name) + '</td>';
    }

    this.html += '<td>' + (this.winner.grade || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.last_name || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.first_name || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.street || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.city || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.state || 'N/A') + '</td>';
    this.html += '<td>' + (this.winner.zip || 'N/A') + '</td>';
    if (show_country) {
        this.html += '<td>' + this.winner.country + '</td>';
    }
    this.html += '<td>' + this.winner.prize_name + '</td>';
    this.html += '</tr>';
    return this.html;
};


/*********************** CSV EXPORT *****************************/
function exportToCSV(args) {
    var rows = []; // the rows for the csv export
    var csvContent = "School,Grade,Last Name,First Name,Address,City,State,Zip,Prize Won\n"; // the baisc csv file
    var universalBOM = "\uFEFF";
    // TODO add headers
    $.each($("tr.winner"), function(index, item) {
        item = $(item); // cast to jquery;
        var row = [];
        $.each(item.find("td"), function(index, item) {
            row.push('"' + $(item).text() + '"');
        });
        rows.push(row); // add the row to the csv export
        row = row.join(",");
        csvContent += row + "\n";
    });
    
    var hiddenElement = document.createElement('a');
    hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
    hiddenElement.target = '_blank'; // in a new tab
    hiddenElement.download = 'raffle-winners-report.csv'; // with this file_name
    hiddenElement.click(); // and click it
}