/*
 *  report.js Master JavaScript file for https://mashpia.com/reports/shipping/index.php
 *
 *  supports setting "debug" variable.
 *
 *  requires get_report_options() to be set in the report to parse the report configuration....
 *  
 *  sets event listeners to buttons on page load
 *
 *  Last updated 12/13/2017 by Menachem Hornbacher
 *
 *  functions:
 *      generate_report():
 *          uses get_report_options() to ajax the report from the server and load it into the page
 *
 *      print_report():
 *          paramatizes get_report_options() and opens printed version in another tab
 *
 *      toggle_shipped(event):
 *          set in generate_report(), this is an event listener for shipping checkboxes
 *
 *      toggle_shipped_bulk(event):
 *          set in generate_report(), this is an event listener for tge master switch which flips all the shipping checkboxes in the schools div to be the same way.
 *
 */

var debug; // make sure that debug is defined
var admin;
var shipments;
var shipment_modal;
// set up the buttons
$(document).ready(function(){
    $("#generate").click(report.generate_report);
    $("#print").click(report.print_report);
    $("#print_shipping").click(report.print_report);
}); // end document loading code

// global report object...
var report = {
    
    refresh_shipments: function(school_id){
        var school_table = $("#school_"+school_id)[0];
        shipments.get_shipments(school_id, function(shipment_list) { // get the list of schools....           
            $.each($(school_table).find(".shipment_dropdown"), function(index, td){ // find each location for a dropdown
                var add_button = "<i class='fa fa-plus' data-school_id='"+school_id+"'></i>";
                var disabled;
                if(get_report_options().shipments.hachayols){
                    disabled = $(td).parent().find(".hachayol_shipped").val() === "0";
                } else {
                    disabled = !$(td).parent().find(".shipped_toggle")[0].checked;
                }
                $(td).html(
                    shipments.render_shipments(shipment_list, disabled, td.dataset.shipment_id) + add_button
                );
                $(td).find("i.fa.fa-plus").click(function(event){
                    var school_id = $(event.target).parent().parent().parent().parent().parent()[0].dataset.school_id;
                    shipment_modal.show({}, school_id); // show a blank modal...
                });
            }); // end foreach .shipment_dropdown
            // render bulk shipment selector
            $(school_table).find(".shipments_select").change(shipments.update_shipment);

            $(school_table).find( ".bulk-shipment-select" ).html(
                shipments.render_shipments(shipment_list, false, "")
            );
            $(school_table).find( ".bulk-shipment-select .shipments_select" ).change( report.bulk_shipment_select )
        }); // end get_shipments callback
    },

    bulk_shipment_select: function( event ) {
        var dropdowns = $(event.target).parent().parent().parent().parent().find("td .shipments_select");
        var shipment_id = event.target.value;
        var ajaxArray = []; updatedDropdowns = [];
        
        $.each( dropdowns, function( index, dropdown ) {
            var row = $(dropdown).parent().parent();
            if ( !(event.target.value === dropdown.value) && row.find(".shipped_toggle")[0].checked ) {
                ajaxArray.push( shipments.get_ajax( row ) );
                updatedDropdowns.push( dropdown );
                // dropdown.value = event.target.value;
                // $(dropdown).change();
            }
        });
        var postData = { shipment_id: shipment_id, ajax: ajaxArray };
        $.post("../ajax/add_or_move_shipment.php", postData, function( data ) {
            updatedDropdowns.forEach( function(dropdown){ dropdown.value = shipment_id });
        });
    },
    
    generate_report: function() {
        $("div#report").html("<div class='loader'></div>");// show the loading spinner
        $.ajax({
            type: "POST",
            url: "ajax/report.php" + (debug ? "?debug=true" : ""),
            data: get_report_options(), // must be defined or code will crash
            success: function(data){
                $("div#report").html(data); // load the data into the page
                $(".shipped_toggle").change(report.toggle_shipped); // set an onchnage listener for the checkboxes
                $(".shipped_toggle_bulk").change(report.toggle_shipped_bulk); // add an onchange listener for the toggles
                $(".missing a").click(report.mark_missing);
                $(".yearly_gift_shipping").click(report.toggle_gift_shipping);
                // make sure we have the shipments object loaded up...
                if (shipments !== undefined && admin) { // if the shipments object is set
                    $.each($(".school"), function(index, school_table){ // go through all the schools
                        school_id = school_table.dataset.school_id; // get the school id
                        report.refresh_shipments(school_id);
                    }); // end foreach .school
                } // end if shipments....
                
            },
            error: function() { $("div#report").html("Server error while generating report. Please contact support for further assistance"); }
        });
    },
    // print the report in a new tab
    print_report: function(event) {
        event.preventDefault(); // prevent the browser from opening the link
        // get the data from the dynamic filters
        var data = get_report_options();
        // allow for shipped only as an option
        if (event.target.dataset.shipped_only) { // if we are printing a report that only includes shipped info
            data.shipping_status = "shipped"; // update the filter if that button was pressed
        }
        // set the url and open it
        var params = $.param(data);// paramaratize the data from the generated report
        var url = event.target.href + "?" + (debug ? "debug=true&" : "") + params; // genearate the url
        window.open(url, '_blank'); // open it in a new tab
        //win.focus(); // open the new window
    },
    // ajax call when the shipping toggles are checked
    toggle_shipped: function(event) {
        var checked = event.target.checked; // get the status of the checkbox
        var ajax_info = event.target.dataset.ajax; // and the pre-minified params
        $.post("../ajax/mark_shipped.php", {checked: checked, params: [ ajax_info ]}, function(data){
            if (debug) {console.log(data);} // log the response to the console if we are in debug mode
            data = JSON.parse(data); // parse the data as JSON
            if (data.success === false) { // if the server could not update the shipping status
                event.target.checked = !event.target.checked; // undo the check/uncheck
                return alert(data.error); // and show the user the error provided by the server in an alert
            }
            return report.toggle_shipped_ui( event.target );
        }); // end ajax call
    }, // end toggle shipped
    
    toggle_shipped_ui: function( target ) {
        var checked = target.checked;
        var row = $(target).parent().parent().parent();
        row.find(".status").text(checked ? "Shipped" : "Not Shipped");
        row.find(".missing a").css({"display": checked ? "inline-block" : "none"});
        row.find(".shipments_select").val("");
        // toggle the disabled status...
        if (!checked) { row.find(".shipments_select").attr("disabled", 'disabled'); } 
        else { row.find(".shipments_select").removeAttr("disabled"); }
    },
    
    // master swich to toggle all the shipping checkboxes for the school
    toggle_shipped_bulk: function(event) {
        // get all the checkboxes by going up to the schools div and finding all the checkboxes
        var checkboxes = $(event.target).parent().parent().parent().parent().find("table input.shipped_toggle"); // add another layer for toggle-third container...
        var bulk_checked = event.target.checked; // get what we want to set them all to
        var ajaxArray = []; var changedCheckboxes = [];
        if(debug) {console.log("bulk toggle", checkboxes);} // log it out for inspection in debug mode
        $.each(checkboxes, function(index, item){ // go through each checkbox
            if (item.checked != bulk_checked) { // if the item is different from the bulk toggle
                ajaxArray.push( item.dataset.ajax ); changedCheckboxes.push( item );
            }
        }); // end foreach checkbox
        
        if ( ajaxArray.length === 0 ) return false;

        $.post("../ajax/mark_shipped.php", {checked: bulk_checked, params: ajaxArray}, function(data) {
            data = JSON.parse( data )
            if (data.success === false) {
                changedCheckboxes.forEach( function( item ) { item.checked = !bulk_checked; } );
                return alert(data.error);
            }
            return changedCheckboxes.forEach( function( item ) { 
                item.checked = bulk_checked;
                report.toggle_shipped_ui( item );
            });
        });
    }, // end toggle_shipped_bulk
    
    mark_missing: function(event) {
        var ajax_info = event.target.dataset.ajax; // and the pre-minified params
        $.post("../ajax/mark_shipped.php", {checked: false, params: ajax_info, qty: 0}, function(data){
            if (debug) {console.log(data);} // log the response to the console if we are in debug mode
            data = JSON.parse(data); // parse the data as JSON
            if (data.success === false) { // if the server could not update the shipping status
                alert(data.error); // and show the user the error provided by the server in an alert
            } else {
                var row = $(event.target).parent().parent();
                row.find(".status").text("Not Shipped");
                row.find(".shipment_text").html("N/A");
                if(row.find(".shipped_toggle")[0]) { row.find(".shipped_toggle")[0].checked = false;}
                if(row.find(".hachayol_shipped")[0]) { row.find(".hachayol_shipped").val("0");}
                row.find(".shipments_select").attr("disabled", 'disabled');
                $(event.target).css({"display": "none"});
            }
        }); // end ajax call
    }, // end mark_missing function
    
    
    toggle_gift_shipping: function (event){
        var school_id       = event.target.dataset.school_id;
        var shipping_method = event.target.value;
        $.post("ajax/change_gift_shipping.php",
            {school_id: school_id, shipping_method: shipping_method},
            function(data) {
                data = JSON.parse(data);
                if (!data.success) { alert ("Sorry but we could not save your shipping prefrence at this time, please try again later"); }
            }
        );
    }
}; // end gloabal report wrapping object...
