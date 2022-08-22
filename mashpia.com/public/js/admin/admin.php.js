function change_class(class_id) {
    document.getElementById("class_id").value = class_id;
    document.forms["class_form"].submit();          
}

function change_school(school_id) {
    document.getElementById("school_id").value = school_id;
    document.forms["school_form"].submit();         
}

function change_child(child_id) {
    document.getElementById("child_id").value = child_id;
    document.forms["child_form"].submit();
}
// if the school is not registered submit the prefilled form
function check_school_registered() {
    // if (school_registered == "false" && admin_id != 2) {
    //     document.forms["registration_form"].submit();
    // }
}

// validate input   
function validation(){

    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    var address = document.getElementById('admin_email').value;
    
    if (document.getElementById('first').value == '') {
        document.getElementById('first').focus();
        alert("First Name is mandatory.");
        return false;
    }   
    else if (document.getElementById('last').value == '') {
        document.getElementById('last').focus();
        alert("Last Name is mandatory.");
        return false;
    }
    else if (document.getElementById('admin_phone_home').value == '') {
        document.getElementById('admin_phone_home').focus();
        alert("Home phone is mandatory.");
        return false;
    }
    else if (document.getElementById('admin_email').value == '') {
        document.getElementById('admin_email').focus();
        alert("Email is mandatory.");
        return false;
    }
    else if  (reg.test(address) != true) {                  
        document.getElementById('admin_email').focus();
        alert("Invalid email address.");
        return false;               
    }
    else {
        return true;            
    }
}

function popUp() {
    alert("Please Note: If you have changed your username or password you will automatically be logged out and you will need to login with the new username / password that you entered.");
}