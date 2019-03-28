$(document).ready(function(){
    
    /********************** ON LOAD SCRIPTS ********************/
    getChapsTable(); // load the table on page load...
    
    /********************** EVENT HANDLERS ********************/
    $("#generate_chaps_report").click(getChapsTable);
    $("select#school_id").change(getChapsTable);
    //$(".full").click(toggleSSize);
    $("#chap_modal input.chap_type").click(updateTotal);
    $("input.sweater").change(updateTotal);
    $(".sweater").click(showSweater);
    $("#chap_modal .close").click(hideChapModal);
    $("a#create_chaperone").click(showChapModal);
    $("#chap_modal form").submit(submitChaperone);
    $("#chap_modal #chap_add").click(addChaperone);
    $("#chap_modal #chap_prev").click(prevChaperone);
    $("#chap_modal #chap_next").click(nextChaperone);

    // **************** APPLICATION STATE ****************//    
    var state; // set in showChapModal.
    
    // **************** LOAD PAGE ****************// 
    // load the table of chaperones...
    function getChapsTable() {
        // show the loading circle....
        $("#chaperones").html("<div class='loader'></div>");
        // set the POST data array....
        var data = {
            school_id: $("select#school_id").val()
        };
        $.post("/ajax/chidon/getChaperones.php", data, function(table){
            $("#chaperones").html(table); // set the result to the UI
            $("#chaperones .delete").click(deleteChaperone);
            $("#chaperones .edit").click(function(event){ // when the edit button is pressed enable editing the chaperone.
                showChapModal(event.target.dataset.chap_id);
            });
        });
    }
    
    // **************** LOAD CHAPERONES ****************// 
    // loads a chaperone into the modal....
    function loadChaperone(chap_id) {
        // load the details of the chaperone from the server...
        $.post("/ajax/chidon/getChaperone.php", {chap_id: parseInt(chap_id)}, function(chap_info) {
            try {
                chap_info = JSON.parse(chap_info);
            } catch (e) {
                alert("Error CH-CHAP-001: Cound not understand Chaperone response from the server.");
                return hideChapModal(); // use return so we do not go further down the function....
            }
            
            if (!chap_info.success) {
                alert(chap_info.error);
                return hideChapModal(); // use return so we do not go further down the function....
            }
            
            setChaperone(chap_info.chaperone);
        });
    }
    // set the chaperone in the modal...
    function setChaperone(chap) {
        $("#chap_modal input#first_name").val(chap.first_name);
        $("#chap_modal input#last_name").val(chap.last_name);
        $("#chap_modal input#number").val(chap.phone);
        $("#chap_modal input#email").val(chap.email);
        $("#chap_modal input#dob").val(chap.dob);
        $("#chap_modal select.s_size").val(chap.sweater_size ? chap.sweater_size : "");
        // disable the sweater size if none was selected/paid for.
        //$("#chap_modal select.s_size").attr("disabled", !chap.sweater_size);
        $("#chap_modal select.chidon_type").val(chap.chidon_type);
        // update the Accomidation info...
        $("#chap_modal input#accName").val(chap.acc_name);
        $("#chap_modal input#accAddress").val(chap.acc_address);
        //$("#chap_modal input#accCrossSt").val(chap.acc_cross_st);
        $("#chap_modal input#accPhone").val(chap.acc_phone);

        let type = chap.chidon_type;
        $("#chap_modal input.chap_type_" + type).trigger('click');
        let vehicle = chap.vehicle;
        $("#chap_modal input.vehicle_" + vehicle).trigger('click');

        if (chap.chap_id) {
            $("#chap_modal input#chap_id").val(chap.chap_id);
        }
        
        // if ($("input#action").val() == "create") {
        //     $('#chap_modal .full_' + chap.full_program).attr("checked", true);
        //     $('#chap_modal .full_' + chap.full_program).change();
        //     $("input.sweater").attr("checked", (chap.sweater && chap.full_program === 0)); // persist the swater check
        //     $("input.sweater").change();
        //     //set the correct sweater size...
        //     if (chap.sweater && chap.full_program === 0) {
        //         $("select.s_size_no").val(chap.sweater_size);
        //     } else if (chap.full_program == 1) {
        //         $("select.s_size_yes").val(chap.sweater_size);
        //     }
        // }
        
        // show the modal...
        modal($("#chap_modal")).show(); // show the modal...
    }
    
    // **************** CREATE/EDIT CHAPERONES ****************// 
    // handle creating and updating the chaperone...
    function submitChaperone(event){
        event.preventDefault();
        var action = $("input#action").val(); // load the action...
        var data = readChaperoneForm( false ); // this also loads the CC info into the state.
        // make sure the form is good.
        if (!data) { return false; }
        
        if (action == "create") {
            state.chaperones[state.index] = data; // update the current chaperone...
            state.school_id = $("select#school_id").val(); // add the school id to the post request...
            // post the response to the server.
            $.post("/ajax/chidon/createChaperones.php", state, function(raw_response) {
                response = JSON.parse(raw_response);
                if (!response.success) {
                    alert(response.error);
                } else {
                    alert(response.message);
                    getChapsTable();
                    hideChapModal();
                }
            });
        } else {
            $.post("/ajax/chidon/editChaperone.php", data, function(raw_response){
                response = JSON.parse(raw_response);
                if (!response.success) {
                    alert(response.error);
                }
                getChapsTable();
                hideChapModal();
            });
        }
    }
    // add another chaperone to this registration...
    function saveChaperone() {
        var current_chaperone = readChaperoneForm(true);
        
        if (!current_chaperone) {
            alert("Please finish adding this chaperone before moving to another one."); return false;
        }
        
        state.chaperones[state.index] = current_chaperone;
        
        return true;
    }
    // read the form in the modal....
    function readChaperoneForm(skip_validation) {
        var action = $("input#action").val();
        if ( !$("#chap_modal input.chap_type:checked").length ) {
            alert("You must indicate what type of chaperone you are creating (chaperone / walking counselor).");
            return false;
        }
        // setup the initial data block...
        var data = {
            school_id:      $("select#school_id").val(),
            chap_type:      $("#chap_modal input.chap_type:checked").val(),
            first_name:     $("input#first_name").val(),
            last_name:      $("input#last_name").val(),
            phone:          $("input#number").val(),
            email:          $("#chap_modal input#email").val(),
            dob:            $("#chap_modal input#dob").val(),
            chidon_type:    $("select.chidon_type").val(),
            acc_name:       $("#chap_modal input#accName").val(),
            acc_address:    $("#chap_modal input#accAddress").val(),
            //acc_cross_st:   $("#chap_modal select#accCrossSt").val(),
            acc_phone:      $("#chap_modal input#accPhone").val(),
            vehicle:        $("#chap_modal input.vehicle:checked").val(),
            total:          calcTotal() // get the total for just this chaperone and set it to the total when we are reading the form.
        };

        // get the sweater size...
        if (action == "edit") {
            data.sweater_size = $(".edit_chidon_info select.s_size").val();
            if ( data.sweater_size ) data.sweater = 1;
            else data.sweater = 0;
            data.chap_id        = $("input#chap_id").val();
            return data; // we have all the information needed at this point for editing.. so lets just return the data....
        } else {
            if ($("input.sweater").is(":checked")) {
                data.sweater = 1;
                data.sweater_size = $(".create_chidon_info select.s_size").val();
            } else {
                data.sweater = 0;
            }
        }
        
        //data.full_program = parseInt($('#chap_modal .full:checked').val());
        data.full_program = 1; // automatically part of full program as of 2019
        
        // create a new chaperone...
        // if ($(".full:checked").val() == "0" && $("input.sweater").attr("checked")) { // if they are not signing up and are buying a sweater...
        //     data.sweater_size = $("select.s_size_no").val();
        //     data.sweater = 1;
        // } else if ($(".full:checked").val() == "1") {
        //     data.sweater_size = $("select.s_size_yes").val();
        //     data.sweater = 1;
        // } else {
        //     data.sweater = 0;
        // }
        
        // validate that all fields are filled out.
        for (var field in data) {
            if (data.hasOwnProperty(field) && data[field] === "") { // if it is a property of the chaperone and it is blank...
                alert("You must enter data for all the fields."); return false;
            }
        }
        
        // validate that the radio buttons are checked....
        if (data.vehicle === undefined) {
            alert('You must select if you have a vehicle or not for the record.'); return false;
        }
        
        // if (isNaN(data.full_program)) { // since this is parsed as an int we get NaN rather then undefined.
        //     alert('You must select if you will be a part of the full program or not for the record.'); return false;
        // }
        
        if (!skip_validation) { // if we are not skipping the validations...
            if (!$('#chap_modal input#terms').attr("checked")) { // make sure that they aggree to the charge...
                alert('You must indicate your acceptance of the terms' + (amount > 0 ? " and charges" : "") + "."); return false;
            }
        }
        
        state.cc_info = {};
        var amount = calcTotal(true);
        if ( amount ) {
            state.cc_info.amount = amount;
        }
        // if (amount > 0) {
        //     state.cc_info.amount = amount;
        //     state.cc_info.ccnum = $("#chap_modal input#cardnumber").val().replace(/\D+/g, ""); // remove all non digit characters (whitespace and letters)
        //     state.cc_info.ccexp = $("#chap_modal input#exp").val();
        //     state.cc_info.cczip = $("#chap_modal input#zip").val();
        // } else {
        //     state.cc_info = {};
        // }
        
        return data;
    }
    
     /********************** DELETE CHAPERONES ********************/
    function deleteChaperone( event ) {
        event.preventDefault();
        var conf = confirm('Are you sure you want to delete this chaperone?');
        if (conf) {
            var id = event.target.dataset.chap_id;
            var tr = this;
            $.post('/ajax/delChap.php', { id : id }, function( success) {
                if (parseInt(success) == 1) {
                    $(tr).parent().parent().remove();
                } else {
                    alert('Error deleting.');
                }
            });
        }
    }

    /********************** ALLOW FOR MORE THEN ONE CHAPERONE ********************/
    function addChaperone() {
        if (saveChaperone()){
            state.chaperones.push({});
            state.index = state.chaperones.length - 1;
            clearChapModal();
            $("#chap_prev").show();
            $("#chap_next").hide();
        }
    }
    // navigate to the previous chaperone
    function prevChaperone() {
        if (saveChaperone() && state.index >= 0) {
            state.index -= 1;
            $("#chap_next").show();
            setChaperone(state.chaperones[state.index]);
            
            if (state.index === 0) {
                $("#chap_prev").hide();
            }
        }
    }
    // navigate to the next chaperone...
    function nextChaperone() {
        if (saveChaperone() && state.index < state.chaperones.length) {
            state.index += 1;
            $("#chap_prev").show();
            setChaperone(state.chaperones[state.index]);
            
            if (state.index === state.chaperones.length - 1) {
                $("#chap_next").hide();
            }
        }
    }
    
    /********************** CONTROL THE MODAL ********************/
    // show the chaperone modal...
    function showChapModal(chap_id) {
        // make sure that a school is selected.
        if (!$("select#school_id").val() && !parseInt(chap_id)) {
            $("select#school_id").focus(); // go to the school...
            alert("Please Select A school!");
            return false;
        }
        
        // reset the application state...
        state = {
            chaperones: [{}], // the chaperones that they might want to add....
            cc_info:    {}, // the cc info to pay with...
            index:      0// the index of the one they are looking at.
        }; // the state of the mini application will be stored in this variable...
        
        if (parseInt(chap_id)) { // if the chap id is a number and not an event object...
            $("span#heading").text("Edit");   $("input#action").val("edit");
            $(".create_chidon_info").hide(); $("div.edit_chidon_info").show();
            $("#chap_modal .submit").val("Update Chaperone");
            loadChaperone(chap_id);
        } else {
            $("span#heading").text("Create");   $("input#action").val("create");
            $(".create_chidon_info").show(); $("div.edit_chidon_info").hide();
            $("#chap_modal .submit").val("Create Chaperone(s)");
            modal($("#chap_modal")).show(); // show the modal...
        }
    }
    // hide the chaperone modal...
    function hideChapModal() {
        // if they have more then one chaperone, then make sure they actually want to close the modal.
        if (state.chaperones.length <= 1 || confirm("Are you sure you want to close this modal? You will lose any unsaved data.")) {
            modal($("#chap_modal")).hide(); // hide the modal...
            $("#chap_prev").hide();
            $("#chap_next").hide();
            clearChapModal();
        }
    }
    // clear all info in the modal
    function clearChapModal() {
        $("#chap_modal input[type='text']:not(.cc)").val("");
        $("#chap_modal input[type='date']").val("");
        $("#chap_modal input[type='checkbox']").attr('checked', false);
        $("#chap_modal input[type='radio']").attr('checked', false);
        $("#chap_modal .showAgree input").attr('required', false);
    }
    
    /********************** MODAL EVENTS ********************/
    // show/hide the sweater size when a .full is clicked...
    // function toggleSSize(event) {
    //     $(event.target).parent().find("div.s-size").show(); // show the options for the selected one
    //     $(event.target).parent().parent().siblings().find("div.s-size").hide(); // hide all the other ones...
    //     updateTotal();
    // }

    function showSweater() {
        $(".s-size").show();
    }
    
    /********************** HANDLE THE MONEY ********************/
    // update the total on the page...
    function updateTotal() {
        var total = calcTotal(true);
        $("span.total").text(total); //calculate the total and update the page with that info...
        // show the CC info if there is money required
        //if (total) {
            var message = (total ? "Pay for and " : "") + "Create " + (state.chaperones.length > 1 ? state.chaperones.length + " " : "") + "Chaperone(s)"
            // $("#chap_modal .showAgree input").attr('required', true);
            // $("#chap_modal .showAgree").show();
            $("#chap_modal .submit").val( message );
        // } else {
        //     $("#chap_modal .showAgree input").attr('required', false);
        //     $("#chap_modal .showAgree").hide();
        //     $("#chap_modal .submit").val("Create " + (state.chaperones.length > 1 ? state.chaperones.length + " " : "") + "Chaperone(s)");
        // }
    }
    // calculate the total payment...
    function calcTotal(grand_total) {
        var total = 0;
        //add all other chaperones to the newly computed total...
        if (grand_total === true) {
            for(var i = 0; i < state.chaperones.length; i++) {
                if (i == state.index) {continue;}
                total += state.chaperones[i].total;
            }
        }
        // check if they are attending the chidon
        // if ($(".full:checked").val() === "1") {
        //     total += 100;
        // } else 
        if ($("input.sweater")[0].checked) { // check if they are just buying a sweater
            if ( $("#chap_modal input.chap_type:checked").val() == 2 ) total += 20;
        }
        return total; // return 0 by default....
    }

});