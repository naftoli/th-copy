// set up on page load
$(document).ready(function(){
    $("select#school_id").change(updateRaffle); // set up the initial handler on page load
    $("select#sorting").change(updateRaffle);
    $("a#export-to-csv").click(exportToCSV);
    //$("select#school_id").change(); // auto run the script for the first school?
    get_raffles(); // load the raffles dropdown box
});

function get_raffles() {
    $.post("/raffles/shared/ajax/list_raffles.php", {type: "", ran_only: true}, function(data){
        $("#raffle_select_container").html(data);
        $("select#raffle_id").change(updateRaffle); // now that the raffle_id option is on the screen, set the on change function
    });
}

function updateRaffle() {
    
    $('div#winner_list_container').html("<div class='loader'></div><p id='loader-status'>Getting Data....</p>"); // show the loader
    
    var school_id = $("select#school_id").val();
    var raffle_id = $("select#raffle_id").val();
    var sorting = $("select#sorting").val();
    
    if (!raffle_id) {
        $('div#winner_list_container').html("<br/><br/><br/><p id='loader-status'>Please select a raffle</p>"); // show the error
        return false; // make sure we have a raffle selected before loading anything
    }
    
    $.post("/raffles/shared/ajax/get_raffle_winners.php", {school_id: school_id, raffle_id: raffle_id, single_list: true, sorting: sorting}, function(data){
        $("p#loader-status").text("Parsing Data...."); // update the status on slow devices
        data = JSON.parse(data)[0]; // get the raffle info
        
        console.log(data);
        // if the data is undefined (no raffle selected)
        if (data === undefined) {
            return false;
        }
        $("p#loader-status").text("Generating Table...."); // update the status on slow devices
        
        var html = '<table id="winners-table">';
        html += '<thead><th>School</th> <th>Grade</th> <th>Last Name</th> <th>First Name</th> <th>Address</th> <th>City</th> <th>State</th> <th>Zip</th>';
        if (school_id == 269 || school_id == 61 ) {
            html += '<th>Country</th>';
        }
        html += '<th>Prize Won</th></thead>';
        html += '<tbody>';
        
        for (var i = 0; i < data.winners.length; i++) {
            html += new winner_renderer(data.winners[i]).render((school_id == 269 || school_id == 61));
        }
        
        html += '</tbody></table>';
        
        $('div#winner_list_container').html(html);
    });
}

/*********************** RENDERING OBJECT *****************************/
function winner_renderer(winner) {
    this.winner = winner;
}

winner_renderer.prototype.render = function(show_country){
    // set the address to N/A if it is null.
    if (this.winner.address.street === null) {
        this.winner.address = {street: "N/A", city: "N/A", state: "N/A", zip: "N/A"};
    }
    // render the table row
    this.html = '<tr class="winner">';
    this.html += '<td>' + (this.winner.hachayol_name ? this.winner.hachayol_name : this.winner.school) + '</td>';
    this.html += '<td>' + this.winner.grade + '</td>';
    this.html += '<td>' + this.winner.last_name + '</td>';
    this.html += '<td>' + this.winner.first_name + '</td>';
    this.html += '<td>' + this.winner.address.street + '</td>';
    this.html += '<td>' + this.winner.address.city + '</td>';
    this.html += '<td>' + this.winner.address.state + '</td>';
    this.html += '<td>' + this.winner.address.zip + '</td>';
    if (show_country) {
        this.html += '<td>' + this.winner.address.country + '</td>';
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