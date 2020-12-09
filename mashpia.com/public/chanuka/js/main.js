"use strict";

// $("div.mission-box-container").on("click", (e) => {
//     $("div.mission-box").css("background","#ffce04");
//     localStorage.removeItem("missionNum");
// });

$("div.mission-box").on({
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

$("div.mission-box").on("click", (e) => {
    let elem = e.currentTarget;
    let missionNum = elem.dataset.missionNum;

    if (localStorage.missionNum == missionNum) {
        elem.style.background = "#ffce04"
        localStorage.removeItem("missionNum");
        $("#mission-submit-btn-first").prop("disabled", true);
        return;
    }

    localStorage.setItem("missionNum", missionNum);
    $("input#mission-num-inp").val(missionNum);
    $("div.mission-box").css("background","#ffce04");
    elem.style.background = "#2484c6";
    $("#mission-submit-btn-first").prop("disabled", false);
});

// $("#mission-submit-btn-first").on("click", (e) => {
//     if ($("input#mission-num-inp").val()) {
//         console.log("good!")
//     } else {
//         console.log("not good!")
//     }
// });

$(".mission-form-inp").on("input", (e) => {
    if ($("#first-name-inp").val() && $("#last-name-inp").val() && $("#birthday-inp").val() && $("#school-inp").val()) {
        $("#mission-form-submit-modal").prop("disabled", false);
    }
});


$("#mission-form-submit-modal").on("click", (e) => {
    if ($("#first-name-inp").val() && $("#last-name-inp").val() && $("#birthday-inp").val() && $("#school-inp").val()) {
        $("#stage-1").fadeOut();
        setInterval(() => {
            $("#stage-2").fadeIn();
        }, 350);
        
    }
});

$("#has-account-btn").on("click", () => {
    $("#stage-1").fadeOut();
    setInterval(() => {
        $("#stage-2").fadeIn();
    // $("#login-form").fadeIn();
    }, 350);
    
});

$(".mission-form-inp").on("input", (e) => {
    if ($("#email-inp").val() && $("#password-inp").val()) {
        $("#mission-form-submit-modal-final").prop("disabled", false);
    }
});