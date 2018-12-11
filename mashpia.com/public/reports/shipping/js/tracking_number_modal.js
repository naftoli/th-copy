var shipment_id;

var tracking_numbers = function(){
    
    function refresh() {
        $.post("../ajax/get_tracking_numbers.php", {shipment_id: shipment_id}, function(data) {
            $("#tracking_numbers").html(data);
            $("#add_tracking_number").click(function(){tracking_number_modal.show();});
            $(".tracking_number_edit").click(function(event){
                var info = $.trim($(event.target).parent().text()).split("("); // get the information from the text
                info[1] = info[1].replace(/[{()}]/g, ''); // remove the prenthesies around the provider...
                var tracking_number_id = event.target.dataset.tracking_number_id;   // get the tracking number id
                               
                var tracking_number = {
                    id: tracking_number_id,
                    tracking_number: event.target.dataset.tracking_number,
                    provider: event.target.dataset.tracking_provider
                };
                
                tracking_number_modal.show(tracking_number);
            });
        });
    }
    
    return {
        refresh: refresh
    };
}();

tracking_numbers.refresh();

var tracking_number_modal = function(){
    // private variables...
    var current_tracking_number_id = false;
    var modal = $("#tracking_number_modal");
    
    // set the event handlers
    modal.find(".tracking_number_modal_save").click(submit);
    modal.find(".tracking_number_modal_exit").click(function(){
        hide();
    });
    
    function show(tracking_number) {
        // check if we are creating or editing a current_tracking_number_id....
        if (tracking_number && tracking_number.id) {
            current_tracking_number_id = tracking_number.id; // remember the id passed in
            modal.find("#modal-title").text("Edit Tracking Number");
        } else {
            modal.find("#modal-title").text("Create Tracking Number");
        }
        
        if (tracking_number) {
            modal.find("input#tracking_number").val(tracking_number.tracking_number);
            modal.find("select#shipping_provider").val(tracking_number.provider);
            //modal.find("select#shipping_provider").val(
            //    tracking_number.tracking_link.indexOf("ups.com/") > -1 ? "UPS" : "USPS" // check if the package is from UPS or USPS and set the paramater accordingly...
            //);
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
            current_tracking_number_id = false;
            modal.find("input#tracking_number").val("");
        }, 1000);
    }
    
    function submit() {
        var tracking_number = modal.find("input#tracking_number").val();
        var provider = modal.find("select#shipping_provider").val();
        
        if (!tracking_number) {
            alert("You Must Enter A tracking Number");
            return false;
        }
        
        var data = {tracking_number: tracking_number, provider: provider, shipment_id: shipment_id};
        
        if (current_tracking_number_id) {
            data.tracking_number_id = current_tracking_number_id;
        }
        
        console.log(data);
        
        $.post("/reports/shipping/ajax/tracking_number.php", data, function(result){
            result = JSON.parse(result); // get the JSON response....
            if (!result.success) { // if it failed...
                alert(result.error); return false; // let the user know the error....
            }
            hide();
            tracking_numbers.refresh();
        }); // end post request...
    }
    
    return {
        show: show,
        hide: hide
    };
}();