// only run this when the document is ready

$(document).ready(function(){
    
    var postPrizeData = function(checked, qty, raffle_id, event){
        var prize_id = $("input[name='prize_id']").val();
        
        var post_url = checked ? "/raffles/shared/ajax/add_prize.php" : "/raffles/shared/ajax/remove_prize.php";
        var success = true;
        
        var handleFailure = function(data){
            success = false;
            event.target.checked = !event.target.checked; // revert the check on the box if it fails (only works when the checkbox was affected)
            // if the server returns a new total to us. update the correct ifo
            if (data.total) {
                event.target.value = data.total;
            }
        };
        
        var handleSuccess = function(){
            if (event.target.value != "on" && !checked) { // if 
                event.target.value = 0;
            } else if (!checked){
                $("#prizes input#raffle_qty_" + raffle_id).val(0); // reset the box to 0;
            }
        };
        
        $.ajax({
            type: "POST",
            url: post_url,
            async: false, // this ensures that it returns true or false to the user. at the cost of UI
            data: {
                prize_id: prize_id,
                raffle_id: raffle_id,
                qty: qty
            },
            //context: {event: event, success: success}, // pass the event down so we can change the checked state
            success: function(data){
                data = JSON.parse(data);
                if (!data.success) {
                    alert(data.error); // show the error
                    handleFailure(data);
                } else {
                    handleSuccess();
                }
            },
            error: function(xhr){
                alert("Error: " + xhr.status + ": " + xhr.statusText); // show the user the http error
                handleFailure();
            }
        });
        
        return success;
    };
    
    var handleCheckboxClick = function(event){
        
        var checked = event.target.checked;
        var raffle_id = event.target.id.split("_")[2];
        var qty = parseInt($("#prizes input#raffle_qty_" + raffle_id).val(), 10);
        postPrizeData(checked, qty, raffle_id, event);
    };
    
    var handleQtyInput = function(event){
        var qty = parseInt(event.target.value);
        var raffle_id = event.target.id.split("_")[2];
        var checked = $("#prizes input#raffle_include_" + raffle_id).is(':checked');
        
        if (checked) {
            postPrizeData(checked, qty, raffle_id, event);
        } else if(!checked) {
            alert("You cannot assign quantity for prizes that are not in the raffle");
            event.target.value = 0;
        }
    };
    
    $("#prizes input[type='checkbox']").change(handleCheckboxClick);
    $("#prizes input[type='number']").change(handleQtyInput);

});