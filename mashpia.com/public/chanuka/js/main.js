"use strict";


// by default user not member
let isMember = false;


let success_message = `<h4>Mazal Tov</h4><p>Your chanukah missions have been submitted please check back after Ches Teves the 23 of december to see who the lucky winners are.<br>

<a href="http://mashpia.com/mobile">Continue Here</a>
To have your own family account and be able to complete daily missions earn medals and be promoted in Rank.
</p>`;
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

    if (localStorage.missionNum == missionNum) {
        $("div.mission-box").css("background-image","linear-gradient(to right, #ffce04 , #f47a20)");
        localStorage.removeItem("missionNum");
        $("#mission-submit-btn-first").prop("disabled", true);
        return;
    }

    localStorage.setItem("missionNum", missionNum);
    $("input#mission-num-inp").val(missionNum);
    $("div.mission-box").css("background-image","linear-gradient(to right, #ffce04 , #f47a20)");
    elem.style.background = "#2484c6";
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
    data.task_checked_off = $("#mission-num-inp").val();
    data.new_account = 1;

    let dataJson = JSON.stringify(data);

    $.ajax({
        // url: '../createAccounts.php',
        url: './functions/config.php',
        method: 'post',
        data: {dataJson},
        success: function (res) {
            //  check if respons success
                // send email

                //show the user
                $("#modal-body").html(success_message)
            console.log("ajax success!!");
            console.log(res);
        }
    }).fail(function () {
        $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form</p>");
        console.log('Probom with Ajax');
    });
});


// member form submit
$("#mission-form-modal-member").on("submit", (e) => {
    e.preventDefault();

    let flag = true;
    
    // val first name
    if ($("#serial-number-inp").val().length < 2) {
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
    data.task_checked_off = $("#mission-num-inp").val();
    data.new_account = 0;

    let dataJson = JSON.stringify(data);

    $("#mission-form-submit-modal-member").text("Sending Form...");

    $.ajax({
        // url: '../createAccounts.php',
        url: './functions/config.php',
        method: 'post',
        data: {dataJson},
        success: function (res) {
            //  check if respons success

            //show the user
            $("#modal-body").html(success_message)
            console.log("ajax success!!");
            console.log(res);
        }
    }).fail(function () {
        $("#modal-body").html("<p class='text-danger'>There was a problem wail sending the form</p>");
        console.log('Probom with Ajax');
    });
});





    // functions


// email validation function

function valEmail (userEmail) {
    userEmail = userEmail.trim();
    // var emailPattern = /^[A-Za-z1-9.]+@[a-z]+[.][a-z.]+$/;
    var emailPattern = /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/;
    return userEmail.match(emailPattern);
}