// *************
// new ticket page
// *************

// get the additional options panel
var panel = $(".tab-pane#three");

var primaryPanel = $(".tab-pane#one");

// *****************
// Auto-Fill Fields
// *****************

// autofill and hide the following fields if they are set in LocalStorage
var autofill_and_hide_fields = [
    {
        id: 1,
        key: "mashpia_bug_school_name",
        hide: false
    },
    {
        id: 2,
        key: "mashpia_bug_username",
        hide: true
    },
    {
        id: 3,
        key: "mashpia_bug_phone",
        hide: false
    },
    {
        id: 5,
        key: "mashpia_bug_account_type",
        hide: false
    },
    {
        id: 7,
        key: "mashpia_bug_category",
        hide: false
    },
    {
        id: 8,
        key: "mashpia_bug_url",
        hide: true
    },
    {
        id: 9,
        key: "mashpia_bug_child",
        hide: false
    }
];

// go through each one and hide them
for (var i = 0; i < autofill_and_hide_fields.length; i++) {
    var field = autofill_and_hide_fields[i];
    var value = localStorage.getItem(field.key);
    var input = panel.find("input[name='customField[" + field.id + "]'], select[name='customField[" + field.id + "]']");
    if ((field.key && input.length > 0)) { // make sure we have a key, a value in localStorage and the input field is actually on the page... (or it is the URL field)
        localStorage.removeItem(field.key);
        // if there is a value in localstorage... set it to the value of the field
        if (value) {
            input.val(value);
        }
        // optionally hide the field as well...
        if((value && field.hide) || field.id === 8) { // URL is always hidden (id #8)
            input.parent().hide(); // hide the form-group that this input is a part of.
        }
    }
}

// *****************
// Auto-Fill Fields for the Primary page
// *****************

var primary_autofill_fields = [
    {
        name: "name",
        key: "mashpia_bug_name"
    },
    {
        name: "email",
        key: "mashpia_bug_email"
    }
];

// go through the options on the main page...
for (i = 0; i < primary_autofill_fields.length; i++) {
    var field = primary_autofill_fields[i];
    var value = localStorage.getItem(field.key);
    var input = primaryPanel.find("input[name='" + field.name + "']");
    
    if (field.key && value && input.length > 0) {
        localStorage.removeItem(field.key);
        input.val(value);
    }
}