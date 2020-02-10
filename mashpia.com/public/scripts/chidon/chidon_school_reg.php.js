$(document).ready(function(){
    
    /********************** ON LOAD SCRIPTS ********************/
    getChapsTable(); // load the table on page load...
    
    /********************** EVENT HANDLERS ********************/
    $("#generate_chaps_report").click(getChapsTable);
    $("select#school_id").change(getChapsTable);
    //$(".full").click(toggleSSize);
    $("#chap_modal .close").click(hideChapModal);
    $("a#create_chaperone").click(showChapModal);
    $("#chap_modal form").submit(submitChaperone);
    // $("#chap_modal #walk_add").click(addWalking);
    // $("#chap_modal #chap_add").click(addChaperone);
    // $("#chap_modal #chap_prev").click(prevChaperone);
    // $("#chap_modal #chap_next").click(nextChaperone);

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
        $("#chap_modal input#school_id_val").val(chap.school_id);
        $("#chap_modal input#first_name").val(chap.first_name);
        $("#chap_modal input#last_name").val(chap.last_name);
        $("#chap_modal input#number").val(chap.phone);
        $("#chap_modal input#email").val(chap.email);
        $("#chap_modal input#dob").val(chap.dob);
        $("#chap_modal select.s_size").val(chap.sweater_size ? chap.sweater_size : "");
        if (parseInt(chap.chap_type) == 4 && !chap.sweater_size) $("#chap_modal select.s_size").attr('disabled', true); // can't change sweater size so that we don't have to worry about charging

        // update chap type
        let chap_type = chap.chap_type;
        $("#chap_modal input.chap_type_" + chap_type).attr("checked", true);
        $("#chap_modal input.chap_type").attr("disabled", true);
        if ( parseInt(chap.chap_type) == 1 ) {
            $(".super_terms").hide();
            $(".chap_terms").show();
            $(".terms").show();
        } else if ( parseInt(chap.chap_type) == 2 ) {
            $(".chap_terms").hide();
            $(".super_terms").show();
            $(".terms").show();
        } 

        // update chidon type
        let chidon_type = chap.chidon_type;
        if ( chidon_type == 'boys' ) {
            $("#chap_modal select.chidon_type").eq(0).attr("checked", true);
        } else if ( chidon_type == 'girls' ) {
            $("#chap_modal select.chidon_type").eq(1).attr("checked", true);
        }

        // update the Accommodation info...
        $("#chap_modal input#accName").val(chap.acc_name);
        $("#chap_modal input#accAddress").val(chap.acc_address);
        //$("#chap_modal input#accCrossSt").val(chap.acc_cross_st);
        $("#chap_modal input#accPhone").val(chap.acc_phone);
        let vehicle = chap.vehicle;
        $("#chap_modal input.vehicle_" + vehicle).trigger('click');

        if (chap.chap_id) {
            $("#chap_modal input#chap_id").val(chap.chap_id);
        }
                
        // show the modal...
        modal($("#chap_modal")).show(); // show the modal...
    }
    
    // **************** CREATE/EDIT CHAPERONES ****************// 
    // handle creating and updating the chaperone...
    function submitChaperone(event){
        event.preventDefault();
        var action = $("input#action").val(); // load the action...
        var data = readChaperoneForm(); 
        // make sure the form is good.
        if (!data) { return false; }
        
        if (action == "create") {
            // state.chaperones[state.index] = data; // update the current chaperone...
            // state.school_id = $("select#school_id").val(); // add the school id to the post request...
            // post the response to the server.
            $.post("/ajax/chidon/createChaperones.php", { info : data }, function(raw_response) {
                response = JSON.parse(raw_response);
                if (!response.success) {
                    alert(response.error);
                } else {
                    // alert(response.message);
                    alert("Thank you. Your chaperone information has been saved.");
                    getChapsTable();
                    hideChapModal();
                }
            });
        } else {
            $.post("/ajax/chidon/editChaperone.php", data, function(raw_response){
                response = JSON.parse(raw_response);
                if (!response.success) {
                    alert(response.error);
                } else {
                    alert("Thank you. Your chaperone information has been saved.");
                }
                getChapsTable();
                hideChapModal();
            });
        }
    }
    // add another chaperone to this registration...
    // function saveChaperone() {
    //     var current_chaperone = readChaperoneForm(true);
        
    //     if (!current_chaperone) {
    //         alert("Please finish adding this chaperone before moving to another one."); return false;
    //     }
        
    //     state.chaperones[state.index] = current_chaperone;
              
    //     return true;
    // }
    // read the form in the modal....
    function readChaperoneForm() {
        var action = $("input#action").val();
        if ( !$("#chap_modal input.chap_type:checked").length ) {
            alert("You must indicate what type of staff member you are creating! (chaperone / walking supervisor / etc)");
            return false;
        }

        // setup the initial data block...
        let school_id = $("#school_id_val").val() > 0 ? $("#school_id_val").val() : $("select#school_id").val();
        var data = {
            school_id:      school_id,
            chap_type:      $("#chap_modal input.chap_type:checked").val(),
            first_name:     $("#chap_modal input#first_name").val(),
            last_name:      $("#chap_modal input#last_name").val(),
            phone:          $("#chap_modal input#number").val(),
            email:          $("#chap_modal input#email").val(),
            dob:            $("#chap_modal input#dob").val(),
            chidon_type:    $("select.chidon_type").val(),
            acc_name:       $("#chap_modal input#accName").val(),
            acc_address:    $("#chap_modal input#accAddress").val(),
            acc_phone:      $("#chap_modal input#accPhone").val(),
            vehicle:        $("#chap_modal input.vehicle:checked").val(), 
            s_size:         $("#chap_modal select.s_size").val().trim()
        };

        data.walking = 0;
        if ( parseInt( data.chap_type ) == 1 && action != 'edit' ) {
            // check if chap will be walking children
            if ( !$(".walking:checked").length ) {
                alert("You must indicate if you are walking the children home.");
                return false;
            }
            data.walking = $(".walking:checked").val();
        } else if ( parseInt( data.chap_type ) == 2 ) {
            data.walking = 1;
        }

        // get purchases if types 3,4
        if ( parseInt( data.chap_type ) == 3 || parseInt( data.chap_type ) == 4 ) {
            let purchases = [];
            const options = ['extra_sweater', 'id_tag', 'transportation', 'ticket']; 
            for (let option of options) {
                const el = "input#" + option;
                if ( $(el).is(":checked") ) purchases.push( option );
            }
            if ( purchases.length ) data.purchases = purchases;
        }

        // make sure we have a sweater size 
        if ( 
            parseInt( data.chap_type ) != 4 && data.s_size == '' || 
            parseInt( data.chap_type ) == 4 && data.purchases !== undefined && data.purchases.includes('extra_sweater') 
        ) {
            alert("Please choose a sweater size.");
            return false;
        }

        // remove sweater size if type 4 but didn't check off extra sweater
        if ( 
            ( parseInt( data.chap_type ) == 4 && data.s_size != '' && data.purchases === undefined ) || 
            ( data.purchases !== undefined && !data.purchases.includes('extra_sweater') )
        ) {
            data.s_size = '';
        }
        
        if (action == "edit") {
            data.chap_id        = $("input#chap_id").val();
            // return data; // we have all the information needed at this point for editing.. so lets just return the data....
        } 

        // validate that all fields are filled out.
        for (var field in data) {
            if (data.hasOwnProperty(field) && data[field] === "" && field !== 's_size') { // if it is a property of the chaperone and it is blank...
                alert("You must enter data for all the fields." + field); return false;
            }
        }
        
        data.full_program = 1; // automatically part of full program as of 2019
        
        // validate that the radio buttons are checked....
        if (data.vehicle === undefined) {
            alert('You must select if you have a vehicle or not for the record.'); return false;
        }
        
        if ( (data.chap_type == 1 && !$("#terms1").is(":checked")) || (data.chap_type == 2 && !$("#terms2").is(":checked")) ) {
            alert("You must agree to terms!");
            return false;
        }

        // get data for walking supervisor if it exists
        // if ( $("#chap_modal #walking_supervisor").is(":checked") ) {
        //     let school_id = $("#school_id_val").val() > 0 ? $("#school_id_val").val() : $("select#school_id").val();
        //     let supervisor = {
        //         school_id:      school_id,
        //         chap_type:      2,
        //         first_name:     $("input#supervisor_first_name").val(),
        //         last_name:      $("input#supervisor_last_name").val(),
        //         phone:          $("input#supervisor_number").val(),
        //         email:          $("input#supervisor_email").val(),
        //         dob:            $("input#supervisor_dob").val(),
        //         chidon_type:    $("select.supervisor_chidon_type").val(),
        //         acc_name:       $("input#supervisor_accName").val(),
        //         acc_address:    $("input#supervisor_accAddress").val(),
        //         acc_phone:      $("input#supervisor_accPhone").val(),
        //         vehicle:        $("input.supervisor_vehicle:checked").val()
        //     };
        //     // validate that all fields are filled out.
        //     for (let field in supervisor) {
        //         if (supervisor.hasOwnProperty(field) && (!supervisor[field] || supervisor[field] === undefined)) { // if it is a property of the chaperone and it is blank...
        //             alert("You must enter data for all the fields." + field); return false;
        //         }
        //     }
        //     let size = $("select.supervisor_s_size").val();
        //     if ( size ) {
        //         supervisor.sweater = 1;
        //         supervisor.s_size = size;
        //     } else {
        //         supervisor.sweater = 0;
        //     }
        //     data.supervisor = supervisor;
        // } 

        // if (!skip_validation) { // if we are not skipping the validations...
        //     if (!$('#chap_modal input#terms').attr("checked")) { // make sure that they aggree to the charge...
        //         alert('You must indicate your acceptance of the terms' + (amount > 0 ? " and charges" : "") + "."); return false;
        //     }
        // } 
        if ( $("#total_charge").val() ) {
            data.toCharge = parseInt( $("#total_charge").val() );        
        }
        
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
    // function addWalking() {
    //     if (saveChaperone()){
    //         state.chaperones.push({});
    //         state.index = state.chaperones.length - 1;
    //         clearChapModal();
    //         $("#chap_modal input.chap_type").eq(1).attr('disabled', false);
    //         $("#chap_modal input.chap_type").eq(0).attr('disabled', true);
    //         $("#chap_modal input.chap_type").eq(1).trigger('click');
    //         $("#chap_prev").show();
    //         $("#chap_next").hide();
    //     }
    // }
    // function addChaperone() {
    //     if (saveChaperone()){
    //         state.chaperones.push({});
    //         state.index = state.chaperones.length - 1;
    //         clearChapModal();
    //         // $("#chap_modal input.chap_type").eq(0).attr('disabled', false);
    //         // $("#chap_modal input.chap_type").eq(1).attr('disabled', true);
    //         // $("#chap_modal input.chap_type").eq(0).trigger('click');
    //         // $("#chap_prev").show();
    //         // $("#chap_next").hide();
    //     }
    // }
    // navigate to the previous chaperone
    // function prevChaperone() {
    //     if (saveChaperone() && state.index >= 0) {
    //         state.index -= 1;
    //         $("#chap_next").show();
    //         setChaperone(state.chaperones[state.index]);
            
    //         if (state.index === 0) {
    //             $("#chap_prev").hide();
    //         }
    //     }
    // }
    // navigate to the next chaperone...
    // function nextChaperone() {
    //     if (saveChaperone() && state.index < state.chaperones.length) {
    //         state.index += 1;
    //         $("#chap_prev").show();
    //         setChaperone(state.chaperones[state.index]);
            
    //         if (state.index === state.chaperones.length - 1) {
    //             $("#chap_next").hide();
    //         }
    //     }
    // }
    
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
            $("span#heading").text("Edit Staff Member");   $("input#action").val("edit");
            $(".create_chidon_info").hide(); 
            // $("div.edit_chidon_info").show();
            $("#chap_modal .submit").val("Update Staff Member");
            loadChaperone(chap_id);
        } else {
            $("span#heading").text("Register Staff Member");   $("input#action").val("create");
            $(".create_chidon_info").show(); 
            // $("div.edit_chidon_info").hide();
            $("#chap_modal .submit").val("Register Staff Member");
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

    // function showSweater() {
    //     $(".s-size").show();
    // }
    
    /********************** HANDLE THE MONEY ********************/
    // update the total on the page...
    // function updateTotal() {
    //     var total = calcTotal(true);
    //     $("span.total").text(total); //calculate the total and update the page with that info...
    //     // show the CC info if there is money required
    //     //if (total) {
    //         var message = (total ? "Pay for and " : "") + "Create " + (state.chaperones.length > 1 ? state.chaperones.length + " " : "") + "Chaperone(s)"
    //         // $("#chap_modal .showAgree input").attr('required', true);
    //         // $("#chap_modal .showAgree").show();
    //         $("#chap_modal .submit").val( message );
    //     // } else {
    //     //     $("#chap_modal .showAgree input").attr('required', false);
    //     //     $("#chap_modal .showAgree").hide();
    //     //     $("#chap_modal .submit").val("Create " + (state.chaperones.length > 1 ? state.chaperones.length + " " : "") + "Chaperone(s)");
    //     // }
    // }
    // calculate the total payment...
    // function calcTotal(grand_total) {
    //     var total = 0;
    //     //add all other chaperones to the newly computed total...
    //     if (grand_total === true) {
    //         for(var i = 0; i < state.chaperones.length; i++) {
    //             if (i == state.index) {continue;}
    //             total += state.chaperones[i].total;
    //         }
    //     }
    //     // check if they are attending the chidon
    //     // if ($(".full:checked").val() === "1") {
    //     //     total += 100;
    //     // } else 
    //     if ($("input.sweater")[0].checked) { // check if they are just buying a sweater
    //         if ( $("#chap_modal input.chap_type:checked").val() == 2 ) total += 20;
    //     }
    //     return total; // return 0 by default....
    // }

    // show / hide walking supervisor section
    // $("#walking_supervisor").click( function() {
    //     if ( $(this).is(":checked") ) {
    //         calcCharge();
    //         $("#walking_super_form").show();
    //     } else {
    //         $("#total_charge").val( 0 );
    //         $("#walking_super_form").hide();
    //     }
    // });

    // $(".supervisor_s_size").change( function() {
    //     calcCharge();
    // });

    // function calcCharge() {
    //     let total = 0;
    //     if ( $("#walking_supervisor").is(":checked") ) {
    //         total = 20;
    //     }
    //     if ( $(".supervisor_s_size").val() ) {
    //         total += 20;
    //     }
    //     $("#total_charge_span").text( total );
    //     $("#total_charge").val( total );

    // }

    $(".chap_type").click( function() {
        let type = parseInt( $(this).val() );
        if ( type == 1 ) {
            $(".chap_only").show();
            $(".super_terms").hide();
            $(".chap_terms").show();
            $(".terms").show();
            $(".purchases").hide();
        } else if ( type == 2 ) {
            $(".chap_only").hide();
            $(".chap_terms").hide();
            $(".super_terms").show();
            $(".terms").show();
            $(".purchases").hide();
        } else {
            $(".chap_only").hide();
            $(".terms").hide();
            $(".purchases").show();
        }
        if ( type == 3 ) {
            $(".purchases .price").hide();
            $(".purchases .principal").text("(Complimentary)");
            $(".purchases .ticket_vip").text("VIP");
        } else if ( type == 4 ) {
            $(".purchases .price").show();
            $(".purchases .principal").text("");
            $(".purchases .ticket_vip").text("discounted");
        }
    });

    $(".walking").click( function() {
        calcTotal();
    });

    $(".purchases input").click( function() {
        calcTotal();
    });

    const calcTotal = () => {
        let total = 0;
        if ( !$(".chap_type:checked").length ) return false;
        let type = parseInt( $(".chap_type:checked").val() );
        if ( type == 1 ) {
            if ( !$(".walking:checked").length ) return false;
            if ( !parseInt( $(".walking:checked").val() ) ) {
                if ( $("#chap_modal select.s_size").val().trim() !== '' ) total += 20;
            }
        } else if ( type == 4 ) {
            if ( $(".purchases input#extra_sweater").is(":checked") ) {
                total += 20;
            }
            if ( $(".purchases input#id_tag").is(":checked") ) {
                total += 2.5;
            }
            if ( $(".purchases input#transportation").is(":checked") ) {
                total += 10;
            }
            if ( $(".purchases input$#ticket").is(":checked") ) {
                total += 10;
            }
        }
        $(".totalCost").text( total );
        $("#total_charge").val( total );
    }
});