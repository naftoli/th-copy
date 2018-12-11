var report; // expect report to be present...
var shipment_report; // expect report to be present...

var shipment_modal = function(){
    // private variables...
    var current_school_id = false;
    var current_shipment_id = false;
    var modal = $("#shipment_modal");
    
    // set the event handlers
    modal.find(".shipment_modal_save").click(submit_shipment);
    modal.find(".shipment_modal_exit").click(function(){
        hide();
    });
    
    function show(shipment, school_id) {
        current_school_id = school_id; // remember the school id
        // check if we are creating or editing a shipment....
        if (shipment && shipment.shipment_id) {
            current_shipment_id = shipment.shipment_id; // remember the id passed in
            modal.find("#modal-title").text("Edit Shipment");
        } else {
            modal.find("#modal-title").text("Create Shipment");
        }
        
        if (shipment) {
            modal.find("input#name").val(shipment.name);
            modal.find("input#date_shipped").val(shipment.date_shipped /*? shipment.date_shipped : (new Date()).toISOString().split("T")[0]*/);
            modal.find("input#description").val(shipment.description /*? shipment.date_shipped : (new Date()).toISOString().split("T")[0]*/);
        } else {
            //modal.find("input#date_shipped").val((new Date()).toISOString().split("T")[0]); // set the date to the current date whenener the modal is opened....
        }
        // update the CSS to show the modal...
        modal.css({"opacity": "1", "visibility": "visible"});
    }
    
    function hide() {
        // fade out
        modal.css({"opacity": "0"});
        // after one second (modal faded....). Do all the clean up work...
        setTimeout(function(){
            modal.css({"visibility": "hidden"});
            current_school_id = false;
            current_shipment_id = false;
            modal.find("input#name").val("");
            modal.find("input#date_shipped").val("");
            modal.find("input#description").val("");
        }, 1000);
    }
    
    function submit_shipment() {
        var name = modal.find("input#name").val();
        var date_shipped = modal.find("input#date_shipped").val();
        var description = modal.find("input#description").val();
        
        if (!name) {
            alert("You must enter a name");
            return false;
        }
        
        var data = {name: name, date_shipped: date_shipped, school_id: current_school_id, description: description};
        
        if (current_shipment_id) {
            data.shipment_id = current_shipment_id;
        }
        
        console.log(data);
        
        $.post("/reports/shipping/ajax/shipment.php", data, function(result){

            //try {
                result = JSON.parse(result); // get the JSON response....
                if (!result.success) { // if it failed...
                    alert(result.error); return false; // let the user know the error....
                } else if (report) {
                    report.refresh_shipments(school_id); hide(); // refresh the list and hide the modal....
                } else if (shipment_report) {
                    shipment_report.generate_report(); hide(); // refresh the list and hide the modal....
                } else {
                    location.reload();
                }// end if request failed...
            //} catch (e) { // it cannot be parsed to json
            //    alert("Server Error. Please try again later...");
            //} // end try-catch
        }); // end post request...
    } // end submit_shipment() private function....
    
    return {
        show: show,
        hide: hide,
        get_school_id: function(){ return current_school_id; } // testing function to get the school id inside the modal at the moment....
    };
}();