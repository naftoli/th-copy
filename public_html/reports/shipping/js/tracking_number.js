/*
 *  tracking_number.js JavaScript file for the reports in https://mashpia.com/reports/shipping/
 *
 *  supports setting "debug" variable.
 *  gets tracking number
 *
 *  Created 12/18/2017 by Menachem Hornbacher
 *
 *  global objects:
 *      tracking_number: contains all the following functions
 *
 *  functions:
 *      get_tracking_numbers(school_id):
 *          gets the tracking numbers from ajax/tracking_number_list.php
 *
 *          params:
 *              school_id => the school id that we want to load
 *
 *      create_tracking_number(event):
 *          posts the tracking number to "ajax/tracking_number_submit.php" to create a new tracking number and clears the inputs when done
 *
 *          params:
 *              event => the event that was clicked
 *
 *      update_tracking_number(event):
 *          posts the updated tracking number to "ajax/tracking_number_submit.php"
 *
 *          params:
 *              event => the change() event from typing in an existing tracking number field. set in get_tracking_numbers
 *
 *      deliver_tracking_number(event):
 *          sends a post request to "ajax/tracking_number_submit.php" to deliver the package and removes itself from the list
 *
 *          params:
 *              event => the click of the "delivered" button. set in get_tracking_numbers
 */

var tracking_number = function(){
    var tracking_number_type = window.type; // get the tracking number type from the window;
    return {
        get_tracking_numbers: function(school_id) {
            $.post("../ajax/tracking_number_list.php", {school_id: school_id, type: tracking_number_type}, function(data){
                $("#tracking_number_list_"+school_id).html(data); // set the container to include the data
                $("#tracking_number_list_"+school_id+" .tracking_number_row input[type='text']").change(tracking_number.update_tracking_number); // set the change listener for the input fields
                $(".deliver_tracking.button").click(tracking_number.deliver_tracking_number); // set the event listener for the delivered button
                $("#school_"+school_id).find("i").removeClass("fa-spin"); // stop spinning the font-awesome icon
            });
        },
        
        create_tracking_number: function(event) {
            var tracking_number_div = $(event.target).parent().find(".tracking_number"); // go up to the parent and find the value by class
            var description = $(event.target).parent().find(".description"); // go up to the parent and find the value by class
            var school_id = event.target.dataset.school_id;
            // if there is no tracking number. alert the user and don't continue.
            if (!tracking_number_div.val()) {
                alert("Please enter a tracking number"); return false;
            }
            $.post("../ajax/tracking_number_submit.php", {school_id: school_id, tracking_number: tracking_number_div.val(), description: description.val(), type: tracking_number_type}, function(data) {
                data = JSON.parse(data);
                if (!data.success) {
                    alert(data.error);
                } else { // the server said all is good...
                    tracking_number.get_tracking_numbers(school_id);
                    tracking_number_div.val("");    description.val("");
                }
            });
        },
        
        update_tracking_number: function(event) {
            var tracking_number = $(event.target).parent().find(".tracking_number").val(); // go up to the parent and find the value by class
            var description = $(event.target).parent().find(".tracking_description").val(); // go up to the parent and find the value by class
            var tracking_number_id = $(event.target).parent().find(".tracking_number_id").val();
            
            $.post("../ajax/tracking_number_submit.php", {tracking_number_id: tracking_number_id, tracking_number: tracking_number, description: description}, function(data) {
                data = JSON.parse(data);
                if (!data.success) {
                    alert(data.error);
                }
            });
        },
        
        deliver_tracking_number: function(event) {
            var tracking_number_id = $(event.target).parent().find(".tracking_number_id").val();
            // send the data to the server
            $.post("../ajax/tracking_number_submit.php", {tracking_number_id: tracking_number_id, delivered: true}, function(data) {
                data = JSON.parse(data);
                if (!data.success) {
                    alert(data.error);
                } else {
                    $(event.target).parent().remove();;
                }
            });
        }
    }// end return object;
}(); // run function and return result
