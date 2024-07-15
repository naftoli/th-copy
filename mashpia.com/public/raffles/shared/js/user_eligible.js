// set up on page load
$(document).ready(function(){
    $("select#type").change(selectType); // set up the initial handler on page load
    $("select#type").change(); // make sure that it loads if it is set to 'weekly' or 'monthly' by the browser
});

/*
 *  selectType Function
 *
 *  loads the raffle list once a type has been selected
 *
 */
function selectType(event) {
    var type = event.target.value; // get the type
    
    // return false if the type is not set
    if(type === ""){
        return false;
    }
    
    $.post("/raffles/shared/ajax/list_raffles.php", { type: type }, function(data){
        $("#raffle_select_container").html(data);
        $("#eligible_list_container").html(""); // clear the list container
        document.getElementById("generate").addEventListener("click", generate_report); // set up the handler for the generate button
    });
}

/*
 *  selectRaffleId Function
 *
 *  loads the raffle form when the raffle id is selected
 *
 */
function generate_report(event) {
    var raffle_id = $("#raffle_id").val(); // ge the raffle id
    var school_id = $("select#school_id").val();
    var type = $("input:radio[name ='report_type']:checked").val();

    var url = "/raffles/shared/ajax/table_user_eligible.php";
    
    if (debug_mode) {
        url = "/raffles/shared/ajax/table_user_eligible.php?debug=true";
    }
    
    if (test_mode) { // go into test mode (show all eligible users, not just manually entered ones)
        url = "/raffles/shared/ajax/table_user_eligible.php?test=true&debug=true";
    }
    
    $("#eligible_list_container").html("<div class='loader'></div>");
    alert("Please Note: the report may take some time to load, please be patient.");
    
    $.post(url, {raffle_id: raffle_id, school_id: school_id, type: type}, function(data){
        $("#eligible_list_container").html(data);
        $(".eligible-toggle").change(toggleCheckbox);
    });
}

/*
 *  toggleCheckbox Function
 *
 *  event handler for the checkboxes on the page
 *
 */
function toggleCheckbox(event){
    var checked = event.target.checked; // I cannot get this to come into the function scope of the call backs. otherwise this should be what is used to reset it to avoid potenital errors on some browsers
    var name = $(this).attr("name").split(":"); // split the name of the checkbox (user_id:raffle_id)
    
    var params = {
        user_id: name[0], // get the user id
        raffle_id: name[1], // raffle id
        eligible: checked // and if they are eligible
    };
    // log the params we are sending
    
    $.ajax({
        type: "POST", // send a post request
        url: "/raffles/shared/ajax/user_eligible.php",
        data: params,
        success: function(data){
            data = JSON.parse(data);
            if (!data.success) {
                event.checked = !event.checked; // this is provided by the context paramater, 
                alert(data.error); // show the user the error
            }
        },
        error: function handleError(xhr){
            event.checked = !event.checked; // this is the checkbox and is passed in with context property in ajax request since function scope is not a thing in async calls or something
            alert("Error: " + xhr.status + ": " + xhr.statusText); // show the user the http error
        }
    });
}