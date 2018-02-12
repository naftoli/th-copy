/*
 *  report.js Master JavaScript file for https://mashpia.com/reports/shipping/shipments/index.php
 *
 *  supports setting "debug" variable.
 *
 *  requires get_report_options() to be set in the report to parse the report configuration....
 *  
 *  sets event listeners to buttons on page load
 *
 *  Last updated 12/13/2017 by Menachem Hornbacher
 *
 *  global object shipment_report:
 *      functions:
 *          
 */

var debug; // make sure that debug is defined

$(document).ready(function(){
    shipment_report.generate_report();
    $("#generate").click(shipment_report.generate_report);
    
    // button to control showing shipping modal...
    $("#create_shipment").click(function(){
        var school_id = $("select#school_id").val(); // get the school_id from the dropdown
        if (!school_id) {
            alert("Please Select a school before creating a shipment");
            $("select#school_id").focus();
            return false;
        } else {
            shipment_modal.show({date_shipped: ""}, school_id);
        }
    });
});

var shipment_report = function(){ // encapsulate this function and return the results...
    // get_report_options();
    // gets the options set in the header for the report...
    function get_report_options() {
        return {
            school_id: $("select#school_id").val(),
            status: {
                planned: $("input#planned")[0].checked,
                in_transit: $("input#in_transit")[0].checked,
                delivered: $("input#delivered")[0].checked,
                archived: $("input#archived")[0].checked
            },
            start_date: $("input#start_date").val(),
            end_date: $("input#end_date").val(),
        };
    }
    
    function mark_shipped(event) {
        var shipment_id = event.target.dataset.shipment_id; // get the shipment id
        // get the current date and time
        var date_shipped = (new Date).toLocaleString();
        // post it to the server
        $.post("../ajax/shipment.php", {shipment_id: shipment_id, date_shipped: date_shipped}, function(data){
            console.log(data);
            generate_report();
        });
    }
    
    function mark_delivered(event) {
        var shipment_id = event.target.dataset.shipment_id;
        
        $.post("../ajax/shipment.php", {shipment_id: shipment_id, delivered: true}, function(data){
            console.log(data);
            generate_report();
        });
    }
    
    function mark_archived(event) {
        var shipment_id = event.target.dataset.shipment_id;
        
        $.post("../ajax/shipment.php", {shipment_id: shipment_id, archived: true}, function(data){
            console.log(data);
            generate_report();
        });
    }
    
    function generate_report() {
        $("#shipments_report").html("<div class='loader'></div>");
        $.post("ajax/report.php"+(debug ? "?debug=true" : ""), get_report_options(), function(data){
            $("#shipments_report").html(data);
            $(".shipped").click(mark_shipped);
            $(".delivered").click(mark_delivered);
            $(".archived").click(mark_archived);
        });
    }
    // expose the public functions....
    return {
        generate_report: generate_report
    };
}();