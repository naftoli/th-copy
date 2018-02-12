var debug; // make sure debug is defined
var shipping_report = {}; // object to maintain some state on the page

$(document).ready(function(){
    $("input[name='limit']").click(change_limit); // change the ui when the type is changed
    $("a#generate").click(generate_report);
    
    $("select#sort").change(refresh_report);
    $("select#filter").change(refresh_report);
    $("a#refresh").click(refresh_report);
    
    $("a#print").click(print_report);
    $("a#print_shipping").click(print_report);
    
    // change the limit type for the report
    function change_limit(event){
        $(".option").css({"display": "none"}); // set them all to invisible
        $("#option_"+event.target.value).css({"display": "block"}); // make the selected one visible
    }
    // generate the report
    function generate_report() {
        var limit_type = $("input[name='limit']:checked").val();
        // make sure one is selected
        if (!limit_type) {
            alert("You must select a limit type"); return false;
        }
        var school_id = $("select#school_id").val();
        var post_data = {limit: limit_type, school_id: school_id};
        if (limit_type === "dates") {
            post_data.start = $("#date_start").val();
            post_data.end = $("#date_end").val();
            if (!post_data.start || !post_data.end) {
                alert("Please enter a valid start and end date"); return false;
            }
        }
        if (limit_type === "raffles") {
            if (shipping_report.selected_raffles.length === 0) {
                alert("Please select some raffles before generating the form"); return false;
            }
            post_data.raffle_ids = shipping_report.selected_raffles;
        }
        if (debug) {console.log(post_data);}
        // generate the report and load it into the page
        $("div#report").html("<div class='loader'></div>");
        $.ajax({
            type: "POST",
            url: "report.php" + (debug ? "?debug=true" : ""),
            data: post_data,
            success: function(data){
                shipping_report.generated_data = post_data; // save the generated data if successfull to send when the user wants to print
                $("#report_options").css({"height": "110px"});
                handleRefresh(data);
            },
            error: function() {
                $("div#report").html("Server error while generating report. Please contact support for further assistance");
            }
        });
    }
    // get the generated report with the dynamic filters
    function get_filtered_report_data() {
        // get the filters data
        var sort = $("select#sort").val();
        var filter = $("select#filter").val();
        // merge the new data into the generated report
        return $.extend({sort: sort, filter: filter}, shipping_report.generated_data);
    }
    // refresh the report with all the filters
    function refresh_report() {
        $("div#report").html("<div class='loader'></div>");// show the loading spinner
        var post_data = get_filtered_report_data(); // get the filtered data to generate the report
        $.post("report.php"+(debug ? "?debug=true" : ""), post_data, handleRefresh); // send the ajax request with the data
    }
    // even handler for the ajax calls that sets the data into the correct location and sets the event handlers
    function handleRefresh(data) {
        $("#report").html(data);
        // set up event handlers
        $(".mark_shipped").change(mark_shipped);            $(".mark_shipped_bulk").change(mark_shipped_bulk);
        $(".update_tracking").click(update_tracking_number);$(".deliver_tracking").click(deliver_tracking_number);
    }
    /*
     *  EVENT HANDLERS FOR SHIPPING CHECKBOXES
     */
    // mark a single checkbox
    function mark_shipped(event){
        var checked = event.target.checked;
        var dataset = event.target.dataset;
        var data = {marked: checked, user_id: dataset.user_id, raffle_id: dataset.raffle_id, prize_id: dataset.prize_id};
        if(debug){console.log("mark_shipped data", data);}
        $.post("../ajax/shipping/mark_shipped.php" + (debug ? "?debug=true" : ""), data, function(data){
            if(debug){console.log("mark_shipped response", data);}
            data = JSON.parse(data);
            if (!data.success) {
                event.target.checked = !checked;
                alert("Server Error: Could not update shipping status");
            }
        });
    }
    // mark all the checkboxes for a single school
    function mark_shipped_bulk(event) {
        var school_id = event.target.dataset.school_id;
        var checked = event.target.checked;
        $.each($("#table_shipping_" + school_id + " .mark_shipped"), function(index, item){
            if(item.checked != checked){
                item.checked = checked;
                $(item).change();
            }
        });
    }
    
    /*
     *  EVENT HANDLERS FOR TRACKING NUMBER BUTTONS
     */
    
    function update_tracking_number(event) {
        var school_id = event.target.dataset.school_id;
        var tracking_number_id = event.target.dataset.tracking_number_id;
        var tracking_number = $("#tracking_number_"+school_id).val();
        // compile all the params into on object
        var data = {tracking_number: tracking_number, tracking_number_id: tracking_number_id, school_id: school_id, action: "update"};
        $("#update_tracking_"+school_id+" .fa-refresh").addClass("spin");
        // send the data to the server and show an error if it happens
        $.post("../ajax/shipping/tracking_info.php", data, function(data){
            data = JSON.parse(data); // parse the data
            if (debug) {console.log(data);}
            if (!data.success) {
                alert(data.error);  // alert the user
            } else if (!tracking_number_id || tracking_number === "") {
                refresh_report(); return true; // TODO, update dataset params for buttons instead of regenerating report
            }
            setTimeout(function(){ // add a minor delay to show the spinning animation even on a fast network
                $("#update_tracking_"+school_id+" .fa-refresh").removeClass("spin");
            }, 450);
        });
    }
    // mark the tracking number as delivered
    function deliver_tracking_number(event) {
        var school_id = event.target.dataset.school_id;
        var tracking_number_id = event.target.dataset.tracking_number_id;
        // make sure the user knows what they are doing
        if (!tracking_number_id || !confirm("This action will mark the tracking number as deleted and archive it.\n\nAre you sure you still want to do this?")) {
            return false; // if they say no then abort the function
        }
        // compile the data for the request
        var data = {tracking_number_id: tracking_number_id, action: "delivered"};
        // send the request and handle the results
        $.post("../ajax/shipping/tracking_info.php", data, function(data){
            data = JSON.parse(data); // parse the data
            if (debug) {console.log(data);}
            if (!data.success) {
                alert(data.error);
            } else {
                refresh_report(); // TODO, update dataset params for buttons instead of regenerating report
            }
        });
    }
    
    /*
     *  EVENT HANDLERS FOR PRINTING THE REPORT
     */
    function print_report(event) {
        event.preventDefault(); // prevent the browser from opening the link
        // enforce that they generate the report that they want before printing
        if (!shipping_report.generated_data) {
            alert("You must generate a report before you can print it"); return false;
        }
        // get the data from the dynamic filters
        var data = get_filtered_report_data();
        if (event.target.dataset.shipped_only) { // if we are printing a report that only includes shipped info
            data.filter = "shipped"; // update the filter if that button was pressed
        }
        // set the url and open it
        var params = $.param(data);// paramaratize the data from the generated report
        var url = event.target.href + "?" + (debug ? "debug=true&" : "") + params; // genearate the url
        var win = window.open(url, '_blank'); // open it in a new tab
        win.focus(); // open the new window
    }
    
    
});
