// when the page loads, use these functions for the page

$(document).ready(function(){
    /************************ SET UP THE EVENT LISTENERS **************************/
    // raffle form
    $("#prizes input[type='checkbox']").change(handleWeeklyPrizeCheck);
    // prize form
    $("#raffles input[type='checkbox']").change(handleWeeklyRaffleCheck);
    
    /************************ HIT THE SERVER AND GET THE RESULTS **************************/
    var hitServer = function(event, post_url, ajaxData) {
        
        var handleResult = function(data) {
            data = JSON.parse(data);
            if (!data.success) {
                handleFailure(data);
            } else if (data.total !== undefined) {
                $("input#qty-prize_"+ajaxData.prize_id).val(data.total);
                $("input#qty-raffle_"+ajaxData.raffle_id).val(data.total);
            }
        };
        
        var handleFailure = function(data) {
            // fuction items are available in scope but only if called. Not in debug mode. becuase JS.
            if (event.target.type == "checkbox") { // if it is checkbox 
                event.target.checked = !event.target.checked; // undo the check
            }
            if (data) {
                if (event.target.type == "number") {
                    event.target.value = data.total;
                }
                alert(data.error);
            } else{
                alert("Server Error, Please contact Support. Prizes where NOT updated");
            }
            
        };
        
        $.ajax({
            type: "POST",
            url: post_url,
            data: ajaxData,
            success: function(data){handleResult(data);},
            error: function(){handleFailure();}
        });
    };
    /************************ HANDLE THE RAFFLE FORM **************************/
    function handleWeeklyPrizeCheck(event){ // weekly
        // get the params
        var qty = 100; // weekly raffles are always set to this quantity
        var prize_id = event.target.id.split("_")[1];
        var raffle_id = $("input[name='raffle_id']").val(); // get the raffle id from the hidden input field
        // get the url
        var post_url = event.target.checked ? "/raffles/shared/ajax/add_prize.php" : "/raffles/shared/ajax/remove_prize.php";
        // bundle the data
        var data = {prize_id: prize_id, raffle_id: raffle_id, qty: qty};
        // set up the handlers
        hitServer(event, post_url, data);
    }
    
    /************************ HANDLE THE PRIZE FORM **************************/
    function handleWeeklyRaffleCheck(event) { // weekly
        // get the params
        var qty = 100; // weekly raffles are always set to this quantity
        var prize_id = $("input[name='prize_id']").val(); // get the prize id from the hidden input field
        var raffle_id = event.target.id.split("_")[1];
        // get the url
        var post_url = event.target.checked ? "/raffles/shared/ajax/add_prize.php" : "/raffles/shared/ajax/remove_prize.php";
        // bundle the data
        var data = {prize_id: prize_id, raffle_id: raffle_id, qty: qty};
        // set up the handlers
        hitServer(event, post_url, data);
    }
    
});