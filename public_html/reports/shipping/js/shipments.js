/*
 *  shipments.js JavaScript file for the reports in https://mashpia.com/reports/shipping/
 *
 *  supports setting "debug" variable.
 *  Handles managing shipments...
 *
 *  Created 12/27/2017 by Menachem Hornbacher
 *
 *  global objects:
 *      shipments: contains all the following functions
 *
 *  functions:
 *      get_shipments(school_id):
 *          gets the shipments from ajax/get_shipments.php
 *
 *          params:
 *              school_id => the school id that we want to load
 *              callback => a function that will accept the shipments from the API request
 *
 *      render_shipments(shipments):
 *          takes an array of shipment objects and returns a select element in a string.
 *
 *
 */

var debug;

// define shipments as the result of the following function....
var shipments = function(){
    
    // load the shipments via AJAX
    var get_shipments = function(school_id, callback) {
        $.post("../ajax/get_shipments.php", {school_id: school_id}, function(data) { // hit the server
            data = JSON.parse(data); // parse the data
            if (!data.success) { // if the data is bad
                alert(data.error); // show the error from the server
            } else { // all is good
                callback(data.shipments); // run the callback with the data....
            } // end if data is bad...
        }); // end ajax request
    }; // end function
    
    var render_shipments_select = function(shipments, disabled, selected_id) {
        var html = "<select class='shipments_select' " + (disabled ? "disabled" : "") + ">"; // open the select tag
        html += "<option selected disabled value=''>N/A</option>"; // close the select tag
        // add an option for each value....
        for (var i = 0; i < shipments.length; i++){
            var shipment = shipments[i];
            html += "<option value='"+shipment.shipment_id+"' "+ (shipment.shipment_id == selected_id ? "selected" : "") +">"+shipment.name+"</option>";
        }
        html += "</select>"; // close the select tag
        
        return html; // return the result
    }; // end function
    
    function update_shipment(event) {
        var row = $(event.target).parent().parent();
        var ajax = get_ajax( row );
        
        var shipment_id = event.target.value;
        var postData = { shipment_id: shipment_id, ajax: [ ajax ] };
        $.post("../ajax/add_or_move_shipment.php", postData, function( data ) {
            // console.log( postData, data );
        });
    }

    function get_ajax( row ) {
        if (row.find("input[type='checkbox']").length > 0) {
            return row.find("input[type='checkbox']")[0].dataset.ajax;
        }
        return row.find(".hachayol_shipped")[0].dataset.ajax;
    }
    
    return { // expose the following info in the return statement....
        get_shipments: get_shipments,
        render_shipments: render_shipments_select,
        update_shipment: update_shipment,
        get_ajax: get_ajax
    };
}(); // run function and return result (publlc info)
