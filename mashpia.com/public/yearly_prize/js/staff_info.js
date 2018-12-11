/*
 *  JS AJAX SCRIPT FOR staff_info.php
 *
*/

var debug; // make sure that debug is defined

/****************** ON PAGE LOAD ******************/
$(document).ready(function(){
    // run the event listener whenever any of the inputs are changed
    $("select#school_id").change(load_table);
    // inside the ajax form
    $("#type").change(select_staff_type);
    
    $("#add_staff").click(add_staff);
    $("#load_table").click(load_table);
    $("#cancel_modal").click(view_modal.hide);   
    $("#cancel_modal_x").click(view_modal.hide);
    
    $("#modal-form").submit(submit_form);
    
    load_table(); // run the event function
});

/****************** DEFINE FUNCTIONS ******************/

/****************** LOAD THE TABLE VIA AJAX ******************/
var load_table  = function(){
    var school_id = $("select#school_id").val();
    // render the loader
    $("div#staff_info").html("<div class='loader'></div>");
    // get the staff list
    $.post("../ajax/forms/staff_info_table.php" + (debug ? "?debug=true" : ""), {school_id: school_id}, function(data){
        $("div#staff_info").html(data);
        $("a#edit").click(edit_staff);
    });
    // load the class list
    load_classes(school_id);
}; // end get_total_prizes_ajax

/****************** LOAD THE CLASS SELECT FORM AJAX ******************/
var load_classes = function(school_id, class_id){
    if (!school_id) { // if the school id was not passed in
        school_id = $("select#school_id").val(); // get it from the select drop down
    }
    // get the list of classes that they can pick from in the modal (if it makes sense)
    if (school_id) { // make sure we have a school id (from the input or the select box)
        $.post("../ajax/forms/staff_info_class_list.php" + (debug ? "?debug=true" : ""), {school_id: school_id}, function(data){
            $("#class_select").html(data); // update the class select dropdown
        });
    }
    // if a class id was passed in
    if(class_id && class_id !== "0") {
        // set the class id after 75ms to avoid it being reset by jquery tools (trust me, this took a while to find out).
        setTimeout(function(){$("select#class_id").val(class_id);}, 150); // update the class list to point to the right id
    }
};

/****************** EVENT HANDLER FOR THE STAFF TYPE DROP DOWN ******************/
var select_staff_type = function(){
    var selected = $("#type").val();
    
    var types = $.map($('#type option'), function(option){
        return option.value == selected ? "" : option.value;
    });
    
    for(var i = 0; i < types.length; i++){
        var type = types[i]; // get the item
        if (type === "") { continue; } // if the option is selected then skip this code
        $("#position_"+type).css({"visibility": "collapse", "width": "0px", "margin": "0px"}); // hide the advanced options
    }
    
    $(".custom_input").each(function(index, element){
        if(selected == element.id.split("_")[1] && (selected != "Teacher" || $("input#staff_type").val() === "staff")) {
            // scale the inputs to fit the line
            $(".first_row_scale").removeClass("input_half");
            $(".first_row_scale").addClass("input_third");
            $("#position_"+selected).css({"visibility": "visible", "width": "29%", "margin": "1%"}); // show the advanced options
            return false; // exit the loop to prevent rehiding it
        } else {
            // scale the inputs to fill the line
            $(".first_row_scale").removeClass("input_third");
            $(".first_row_scale").addClass("input_half");
        }
    });
};

/****************** EVENT HANDLER FOR THE ADD STAFF BUTTON ******************/
var add_staff = function(){
    var school_id = $("select#school_id").val();
    if (school_id === "") { // make sure that a school is selected
        alert("Please select a school before adding staff"); return false;
    }
    $("input#school_id").val($("select#school_id").val()); // set the school id in the input form
    $("input#staff_type").val("staff");
    
    view_modal.show(); // show the modal
};

/****************** EVENT HANDLER FOR THE EDIT BUTTON ******************/
var edit_staff = function(event){
    var staff_mark = event.target.dataset.mark;
    var staff_id = staff_mark.split("-")[1];    var staff_type = staff_mark.split("-")[0];
    
    // get the values from the page
    var name = $("#name_"+staff_mark).text();   var email = $("#email_"+staff_mark).text();     var position = $("#position_"+staff_mark).text();
    var work_phone = $("#work_phone_"+staff_mark).text();   var cell_phone = $("#cell_phone_"+staff_mark).text();
    var class_id = $("#class_id_"+staff_mark).val();        var school_id = $("#school_id_"+staff_mark).val();
    // get the item in the list to set it to
    var type = "custom"; // default to custom
    if (staff_type != "teacher") {
        var options = $.map($("#type option"), function(option){return option.value;}); // get the options from the dropdown
        $.each(options, function(option){ // go through all the options
            option = options[option];
            if (position === option) { // check if the staff type is the same as the option
                type = option; // then set the type to the option
                return false; // exit the loop
            }
        });
    } else { // not in the staff_info table
        type = staff_type; // set the type to the staff type for the preselected options
    }
    
    // set the data in the form
    view_modal.set_data(
        {staff_id: staff_id, name: name, email: email, cell_phone: cell_phone, position: position, // set the data
        school_id: school_id, staff_type: staff_type, class_id: class_id, work_phone: work_phone}, type, // set the item on the list (TODO fix)
        staff_type != "teacher" // should the dropdown be activated?
    );
    
    view_modal.show(); // show the modal
};

var submit_form = function(event){
    event.preventDefault(); // stop the form from submitting
    // get the information
    var name = $("input#name").val();   var cell_phone = $("input#cell_phone").val();
    var email = $("input#email").val(); var position = $("input#position").val();
    var staff_id = $("input#staff_id").val();       var school_id = $("input#school_id").val();
    var staff_type = $("input#staff_type").val();   var class_id = $("select#class_id").val();
    var work_phone = $("input#work_phone").val();
    
    var data = {staff_name: name, staff_number: cell_phone, staff_work_number: work_phone, staff_email: email, staff_id: staff_id, school_id: school_id, staff_type: staff_type};
    // set the position
    if (staff_type != "teacher") { // if we are not a teacher
        var type = $("#type").val(); // get the type selected
        position = type == "custom" ?  position : type; // only use the position input if the type is custom. otherwise use the value
        data.staff_position = position; // add the position to the post request
    }
    // set the class id
    if (staff_type == "staff" && data.staff_position == "Teacher") {
        data.class_id = class_id;
    } else {
        data.class_id = 0; // set the class_id to 0
    }
    
    // prevent no schools from showing
    if (!school_id && !staff_id) { // make sure that a school is selected
        alert("Please select a school before adding staff"); return false;
    }
    // make sure that a position was selected
    if (staff_type != "teacher" && !data.staff_position || data.staff_position === "") {
         alert("Please select/enter a staff position"); return false;
    }
    phone_regex_string = "^[(]?[0-9]{3}[)]?[ -]?[0-9]{3}[ -]?[0-9]{4}[ ]?[x]?[0-9]*$";
    // validate phone number if provided
    if (!cell_phone.match(phone_regex_string)) { // check for an american number e.g. (555) 555-5555 or 5556667777
        alert("Please enter a valid US phone number (Cell Number)."); return false;
    }
    // validate phone number if provided
    if (work_phone && !work_phone.match(phone_regex_string)) { // check for an american number e.g. (555) 555-5555 or 5556667777
        alert("Please enter a valid US phone number (Work Number)."); return false;
    }
    
    console.log(data);
    //debugger;
    //return false;
    
    // post the form
    $.post("../ajax/forms/staff_info_submit.php" + (debug ? "?debug=true" : ""), // pass the debugging allong
        data, // all the post data
        function(data){ // on success
            data = JSON.parse(data); // parse it to json
            if (data.success) { // check for success
                view_modal.hide(); // hide the modal
                load_table(); // update the table on success
            } else {
                console.log(data); // log the data
                alert(data.error); // show them the error
            }
        }
    );
};

/****************** MODAL SHOW/HIDE ******************/
var view_modal = {
    show: function(){
        $("#view_modal").css({"visibility": "visible"});
        $("#view_modal").css({"opacity": "1"});
    },
    hide: function(){
        $("#view_modal").css({"opacity": "0"});
        setTimeout(function() {
            $("#view_modal").css({"visibility": "hidden"});
            view_modal.clear_data();
        }, 100);
    },
    set_data(data, type, enabled) {
        // set the data to what is passed in
        $("input#name").val(data.name);     $("input#cell_phone").val(data.cell_phone);
        $("input#email").val(data.email);   $("input#position").val(data.position);
        $("input#staff_id").val(data.staff_id); $("input#school_id").val(data.school_id);
        $("input#staff_type").val(data.staff_type); $("input#work_phone").val(data.work_phone);
        // load the classes
        if (data.class_id) {
            load_classes(data.school_id, data.class_id);
        }
        // set the value of the type selector
        $("#type").val(type);   $("#type").change(); // set the type
        // if they select other clear the input box
        if (type != "custom") {
            $("input#position").val(""); // clear the input#position box
        }
        // disable it if it is not an option
        if(enabled) {
            $("#type").removeAttr('disabled');
        } else {
            $("#type").attr('disabled','disabled');
        }
    },
    clear_data(){
        $("input#name").val("");        $("input#cell_phone").val("");
        $("input#email").val("");       $("input#position").val("");
        $("input#staff_id").val("");    $("input#school_id").val("");
        $("select#class_id").val("");   $("input#staff_type").val("");
        $("input#work_phone").val("");
        
        $("#type").removeAttr('disabled'); // make sure it is enabled
        $("#type").val(''); // set the default item to the <select from...> item
        select_staff_type(); // reset the position
    }
}; // make the basic object