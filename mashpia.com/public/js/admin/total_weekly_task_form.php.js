// set up on page load
$(document).ready(function(){
    console.log("it works");
    $(".week-toggle").change(toggleCheckbox);
});

/*
 *  toggleCheckbox Function
 *
 *  event handler for the checkboxes on the page
 *
 */

function toggleCheckbox(event){
    var checked = event.target.checked; // I cannot get this to come into the function scope of the call backs. otherwise this should be what is used to reset it to avoid potenital errors on some browsers
    var params = event.target.name + ":" + (checked ? 1 : 0);
    
    console.log(params);
    $.ajax({
        type: "POST",
        url: "/ajax/yearly_gift/mark_week.php",
        data: {params: params},
        context: this,
        success: function(data){
            data = JSON.parse(data);
            if (!data.success) {
                this.checked = !this.checked; // this is provided by the context paramater, 
                alert(data.error); // show the user the error
            }
            console.log(data);
        },
        error: function handleError(xhr){
            this.checked = !this.checked; // this is the checkbox and is passed in with context property in ajax request since function scope is not a thing in async calls or something
            alert("Error: " + xhr.status + ": " + xhr.statusText); // show the user the http error
        }
    });
}