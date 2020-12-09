"use strict";

// url to post user info
let postToUrl = "http://mashpia.com/chanuka/createAccounts.php";


// by default user not member
let isMember = false;

// message to send user when successfull form
let success_message = `<h4>Mazal Tov!</h4><p>Your chanukah missions have been submitted please check back after Ches Teves the 23 of december to see who the lucky winners are.<br>
                        <a href="http://mashpia.com/mobile">Continue Here
                        To have your own family account and be able to complete daily missions earn medals and be promoted in Rank.
                        </a></p>`;

// set up local storage
localStorage.setItem("missionNums", JSON.stringify([]));


// notes popover on hover

$("div.mission-box-container").on({
    mouseenter: (e) => {
        let elem = e.currentTarget;
        if (elem.querySelector(".notes-div")) {
            elem.querySelector(".notes-div").style.display = "block";
        }
        
    },
    mouseleave: (e) => {
        let elem = e.currentTarget;
        if (elem.querySelector(".notes-div")) {
            elem.querySelector(".notes-div").style.display = "none";
        }
    }
});


// click on mission

$("div.mission-box").on("click", (e) => {
    let elem = e.currentTarget;
    let missionNum = elem.dataset.missionNum;
    let allMissions = JSON.parse(localStorage.missionNums);

    // check if exsists in array -> remove
    const index = allMissions.indexOf(missionNum);
    if (index > -1) {
        allMissions.splice(index, 1);
        localStorage.setItem("missionNums", JSON.stringify(allMissions));
        $("div.mission-box[data-mission-num="+missionNum+"]").removeClass("mission-selected");
        if (allMissions.length == 0) {
            $("#mission-submit-btn-first").prop("disabled", true);
        }
        return;
    }

    allMissions = [...allMissions, missionNum];
    localStorage.setItem("missionNums", JSON.stringify(allMissions));
    $("input#mission-num-inp").val(JSON.stringify(allMissions));
    $("div.mission-box[data-mission-num="+missionNum+"]").addClass("mission-selected");
    $("#mission-submit-btn-first").prop("disabled", false);
});


// toggle forms member - not member
$("#has-account-btn").on("click", () => {
        isMember = true;
        $("#stage-1").fadeToggle();
        $("#stage-2").fadeToggle();       
});

$("#no-account-btn").on("click", () => {
        isMember = false;
        $("#stage-1").fadeToggle();
        $("#stage-2").fadeToggle();
});


// submiting a new user form
$("#mission-form-modal").on("submit", (e) => {
    e.preventDefault();

    let flag = true;
    
    // val first name
    if ($("#first-name-inp").val().length < 2) {
        $("#first-name-msg").removeClass("d-none");
        console.log($("#first-name-inp").val().length);
        flag = false;
    } else {
        $("#first-name-msg").addClass("d-none");
    }

    // val last name
    if ($("#last-name-inp").val().length < 2) {
        $("#last-name-msg").removeClass("d-none");
        flag = false;
    } else {
        $("#last-name-msg").addClass("d-none");
    }

    // val email
    if (!valEmail($("#email-inp").val())) {
        $("#email-msg").removeClass("d-none");
        flag = false;
    } else {
        $("#email-msg").addClass("d-none");
    }

    // if somthing not valid break the function
    if (!flag) {
        return;
    }


    $("#mission-form-submit-modal").text("Sending Form...");

    let data = {};
    data.first_name = $("#first-name-inp").val();
    data.last_name = $("#last-name-inp").val();
    data.email_address = $("#email-inp").val();
    data.tasks =  JSON.parse(localStorage.missionNums);
    data.new_account = 1;
    data.serial_number = false;

    data = JSON.stringify(data);

    $.ajax({
        // url: '../createAccounts.php',
        url: postToUrl,
        method: 'post',
        data: {data},
        success: function (res) {

            if (res.includes("<br />")) {
                console.log("php problem");
                return;
            } 

            res = JSON.parse(res);

            if (res.error) {
                console.log(res.error)
                $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form<br><br><span><b>Error: </b>"+ res.error +"</span></p>")
            } else {
                $("#modal-body").html(success_message)
                console.log("ajax success!!");
            }
           
        }
    }).fail(function () {
        $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form</p>");
        console.log('Problem with Ajax');
    });
});


// member form submit
$("#mission-form-modal-member").on("submit", (e) => {
    e.preventDefault();

    let serialNumber = $("#serial-number-inp").val();

    let flag = true;
    
    // val first name
    if (!valSerialNumber(serialNumber) || serialNumber.length < 7 || serialNumber.length > 7) {
        $("#serial-number-msg").removeClass("d-none");
        flag = false;
    } else {
        $("#serial-number-msg").addClass("d-none");
    }

    // if somthing not valid break the function
    if (!flag) {
        return;
    }


    let data = {};
    data.serial_number = $("#serial-number-inp").val();
    data.tasks =  JSON.parse(localStorage.missionNums);
    data.new_account = 0;
    data.first_name = false;
    data.last_name = false;
    data.email_address = false;

    data = JSON.stringify(data);

    $("#mission-form-submit-modal-member").text("Sending Form...");

    $.ajax({
        // url: '../createAccounts.php',
        url: postToUrl,
        method: 'post',
        data: {data},
        success: function (res) {

            if (res.includes("<br />")) {
                console.log("php problem");
                return;
            } 

            res = JSON.parse(res);

            if (res.error) {
                console.log(res.error)
                $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form<br><br><span><b>Error: </b>"+ res.error +"</span></p>")
            } else {
                $("#modal-body").html(success_message)
                console.log("ajax success!!");
            }
           
        }
    }).fail(function () {
        $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form</p>");
        console.log('Problem with Ajax');
    });
});





    // functions


// email validation function

function valEmail (userEmail) {
    userEmail = userEmail.trim();
    var emailPattern = /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/;
    return userEmail.match(emailPattern);
}

function valSerialNumber (userSN) {
    userSN = userSN.trim();
    var serialNumberPattern = /^[0-9]*$/;
    return userSN.match(serialNumberPattern);
}