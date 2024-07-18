// when the page loads set up the event listeners
$(document).ready(function(){
    $("#school_id").change(load_platoons);
    $("#csv_export").click(csv_export);
    // load up the default classes...
    load_platoons();
    
    function load_platoons() {
        var school_id = $("#school_id").val();
        
        $.post("../ajax/misc/get_platoons.php" + (debug ? "?debug=true" : ""), {school_id: school_id}, function(data){
            $("#classes").html(data);
        });
    }
    
    function csv_export() {
        var rows = []; // the rows for the csv export
        var universalBOM = "\uFEFF";
        var csvContent = "";
        
        $.each($("#eligible_users_report tr"), function(index, item) {
            item = $(item);
            var row = [];
            // check if this is a header row....
            $.each(item.find('th, td'), function(td_index, td) {
                td = $(td); // cast to jquery object
                if (td.find("input[type='checkbox']").length > 0) { // see if we can find a checkbox...
                    row.push(td.find("input[type='checkbox']")[0].checked ? "Yes" : "No");
                } else {
                    row.push('"' + td.text() + '\t"');
                }
            });
            
            rows.push(row); // add the row to the csv export
            row = row.join(",");
            csvContent += row + "\n";
        });
        
        // open the csv with a hidden element....
        var hiddenElement = document.createElement('a');
        hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
        hiddenElement.target = '_blank'; // in a new tab
        hiddenElement.download = 'yearly_prize_report.csv'; // with this file_name
        hiddenElement.click(); // and click it
    }
});
