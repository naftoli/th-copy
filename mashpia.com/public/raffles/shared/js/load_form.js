// once the page is loaded
$(document).ready(function(){
    // when the type is selected...
    $("select[name='type']").change(function(){ // whenever the user changes this
        load_form($(this).val()); // load the form for that type
    });
    // load the default form (weekly) on page load
    load_form($("select[name='type']").val());
});

function load_form(type) {
    $.get("/raffles/"+type+"/forms/raffle_form.php", function(data){// get the form for the type selected
        $("#detailed_inputs").html(data); // set the page to the data
        // when an option from the list is selected
        $("#week").change(function(event){
            fill_name($("option[value="+event.target.value+"]").html()); // update the name field
            const [startDate, endDate] = $(this).val().split(",")
            $("#start_date").val(startDate) // update the start_date field
            $("#end_date").val(endDate) // update the end_date field
            // add 5 days to the end date and update the run_date field
            let run_date = new Date(endDate)
            run_date.setDate(run_date.getDate() + 5)
            $("#run_date").val() // update the run_date field
        });

        $("#start_week").change(function () {
            $("#start_date").val($(this).val()) // update the start_date field
        })

        $("#end_week").change(function () {
            $("#end_date").val($(this).val()) // update the end_date field
        })

        // autofill the name and start/end dates
        if ($("#week").length) $("#week").change()
        if ($("#start_week").length) $("#start_week").change()
        if ($("#end_week").length) $("#end_week").val($("#end_week").children()[3].value).change()

        $("#show_hq").attr("checked", true)
    });
}
// Handle the requested autofill function
function fill_name(text) {
    $("input[name='name']").val(text.replace(/57[0-9]{2} - /, ''));
}