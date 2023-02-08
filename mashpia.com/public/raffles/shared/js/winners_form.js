// set up on page load
$(document).ready(function(){
    $("select#school_id").change(updateRaffle); // set up the initial handler on page load
    $("select#sorting").change(updateRaffle);
    $("a#export-to-csv").click(exportToCSV);
    //$("select#school_id").change(); // auto run the script for the first school?
    get_raffles(); // load the raffles dropdown box
});

function get_raffles() {
    $.post("/raffles/shared/ajax/list_raffles.php", { type: "", ran_only: true, all: true }, function(data){
        $("#raffle_select_container").html(data);
        $("select#raffle_id").change(updateRaffle); // now that the raffle_id option is on the screen, set the on change function
    });
}

function updateRaffle() {
    $('div#winner_list_container').html("<div class='loader'></div><p id='loader-status'>Getting Data....</p>"); // show the loader
    
    var school_id = $("select#school_id").val();
    var raffle_id = $("select#raffle_id").val();
    var sorting = $("select#sorting").val();
    var single_school = $("select#school_id")[0].disabled;
    
    $.post("/raffles/shared/ajax/get_raffle_winners.php", {
        school_id: school_id, raffle_id: raffle_id, single_list: true, sorting: sorting
    }, function(data){
        $("p#loader-status").text("Parsing Data...."); // update the status on slow devices
        raffles = JSON.parse( data ); // get the raffle info
        
        // if the data is undefined (no raffle selected)
        if (raffles[0] === undefined) {
            return $('div#winner_list_container').html("<h3 style='margin-top: 15px; text-align: center'>No Winners Found</h3>");
        }
        $("p#loader-status").text("Generating Table...."); // update the status on slow devices
        
        var html = '<table id="winners-table">';
        html += '<thead>';
        if ( !single_school ) {
            html += '<th>School</th>';
        } else {
            html += '<th>Raffle</th>';
        }
        html += '<th>Grade</th> <th>User ID</th> <th>Soldier Name</th> <th>Full Address</th>';
        // if (school_id == 269 || school_id == 61 ) {
        //     html += '<th>Country</th>';
        // }
        html += '<th>Prize ID</th><th>Prize Won</th></thead>';
        html += '<tbody>';
        
        raffles.forEach( function ( raffle ){
            for (var i = 0; i < raffle.winners.length; i++) {
                html += new winner_renderer( raffle.winners[i] ).render(
                    ( school_id == 269 || school_id == 61 ), 
                    !single_school, raffle.raffle_name
                );
            }
        });
        
        html += '</tbody></table>';
        
        $('div#winner_list_container').html(html);
    });
}

/*********************** RENDERING OBJECT *****************************/
function winner_renderer(winner) {
    this.winner = winner;
}

winner_renderer.prototype.render = function(show_country, show_school, raffle_name){
    // set the address to N/A if it is null.
    if (this.winner.address.street === null) {
        this.winner.address = { street: "N/A", city: "N/A", state: "N/A", zip: "N/A" };
    }
    // render the table row
    this.html = '<tr class="winner">';
    if ( show_school ) {
        this.html += '<td>' + (this.winner.hachayol_name ? this.winner.hachayol_name : this.winner.school) + '</td>';
    } else {
        this.html += '<td>' + (raffle_name) + '</td>';
    }

    this.html += '<td>' + this.winner.grade + '</td>';
    this.html += '<td>' + this.winner.user_id + '</td>';
    this.html += '<td>' + this.winner.first_name + ' ' + this.winner.last_name + '</td>';
    this.html += '<td>' + this.winner.address.street + ' ' + this.winner.address.city + ', ' +
      this.winner.address.state + ' ' + this.winner.address.zip;
    if (show_country) {
        this.html += ' ' + this.winner.address.country;
    }
    this.html += '</td><td>' + this.winner.prize_id + '</td>';
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