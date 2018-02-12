var debug; // make sure debug is defined

// when the page loads set up the event listeners
$(document).ready(function(){
    $("#refresh").click(refresh_page);
    $("#school_id").change(load_platoons);
    $("#csv_export").click(csv_export);
    // load up the default classes...
    load_platoons();
    // refresh the page
    function refresh_page(){
        $("div#eligible_users_report").html("<div class='loader'></div>");
        
        var type = $("#type").val();
        var start = $("#start").val();
        var end = $("#end").val();
        var school_id = $("#school_id").val();
        var class_grade = $("#class_grade").val();
        
        if (end < start) {
            alert("Error: The end date must be after the start date"); return false;
        }
        
        var url = "../ajax/reports/total_weekly_tasks_" + type + ".php" + (debug ? "?debug=true" : "");
        
        $.post(url, {start: start, end: end, school_id: school_id, class_grade: class_grade}, function(data){
            $("div#eligible_users_report").html(data);
            $("#refresh").html("<i class='fa fa-refresh' aria-hidden='true'></i> Refresh");
            $(".week-toggle").change(toggleCheckbox);
            $(".distributed_mark").change(toggleDistributed);
        });
    }
    
    /*
     *  load_platoons Function
     *
     *  load the list of platoons for the page
     *
    */
    
    function load_platoons() {
        var school_id = $("#school_id").val();
        
        $.post("../ajax/misc/get_platoons.php" + (debug ? "?debug=true" : ""), {school_id: school_id}, function(data){
            $("#classes").html(data);
        });
    }
    
    
    
    /*
     *  toggleCheckbox Function
     *
     *  event handler for the checkboxes on the page
     *
    */
   
   function toggleCheckbox(event){
        var checked = event.target.checked;
        var params = event.target.name + ":" + (checked ? 1 : 0);
        
        if(debug) {console.log("Params: " + params);}
        
        var type = $("#type").val();
       
        if (type == "combined") {
            var user_id = params.split(":")[0]; // get the user_id
            var current_text = $("#total_"+user_id).text().split("/"); // get the current total
            var current_total = parseInt(current_text[0]) + (checked ? 1 : -1); // update it
        }
       
       
        $.ajax({
            type: "POST",
            url: "../ajax/reports/total_weekly_task_mark.php" + (debug ? "?debug=true" : ""),
            data: {params: params},
            //context: this,
            success: function(data){
                data = JSON.parse(data);
                if (!data.success) {
                    event.target.checked = !event.target.checked; // this is provided by the context paramater,
                    //this.checked = !this.checked; // this is provided by the context paramater, 
                    alert(data.error); // show the user the error
                }
                if (type == "combined") {
                    $("#total_"+user_id).text(current_total + "/" + current_text[1]); // update the total if it exists
                }
                if(debug) {console.log(data);}
            },
            error: function handleError(xhr){
                event.target.checked = !event.target.checked; // this is provided by the context paramater,
                alert("Error: " + xhr.status + ": " + xhr.statusText); // show the user the http error
            }
        });
    } // end toggle checkbox
    
    function toggleDistributed() {
        var checked = event.target.checked;
        var params = {id: event.target.dataset.id, mark: checked, type: "user"};
        
        $.post("../ajax/reports/distributed_mark.php", params, function(data){
            data = JSON.parse(data);
            if (!data.success) {
                event.target.checked = !event.target.checked;
            }
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
