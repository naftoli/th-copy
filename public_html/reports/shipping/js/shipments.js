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
        var ajax; // hoist it up in code so we are not confused...
        if ($(event.target).parent().parent().find("input[type='checkbox']").length > 0) {
            ajax = $(event.target).parent().parent().find("input[type='checkbox']")[0].dataset.ajax;
        } else {
            ajax = $(event.target).parent().parent().find(".hachayol_shipped")[0].dataset.ajax;
        }
        var shipment_id = event.target.value;
        
        $.post("../ajax/add_or_move_shipment.php", {shipment_id: shipment_id, ajax: ajax}, function(data){
            console.log(data);
        });
    }
    
    return { // expose the following info in the return statement....
        get_shipments: get_shipments,
        render_shipments: render_shipments_select,
        update_shipment: update_shipment
    };
}(); // run function and return result (publlc info)
