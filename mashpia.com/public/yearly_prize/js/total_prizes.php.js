/*
 *  JS AJAX SCRIPT FOR total_prizes.php
 *
*/

var debug; // make sure that debug is defined

/****************** ON PAGE LOAD ******************/
$(document).ready(function(){
    // run the event listener whenever any of the inputs are changed
    $("#refresh").click(get_total_prizes_ajax);
    $("#ship-all").click(toggle_shipped_whole_page);
    $("#print_link").click(print_form);
    
    
    
    //$("input#yearly_gift_shipping").click(update_shipping_method);
    
    //get_total_prizes_ajax(); // run the event function
});

/****************** DEFINE EVENT LISTENER ******************/
var get_total_prizes_ajax = function() {
    var school_id = $("select#school_id").val();
    var start_date = $("input#start_date").val();
    var end_date = $("input#end_date").val();
    var filter = $("select#filter").val();
    
    $("div#total_prize_report").html("<div class='loader'></div>");
    
    $.post("../ajax/reports/total_prizes.php" + (debug ? "?debug=true" : ""),
        {school_id: school_id, start_date: start_date, end_date: end_date, filter: filter},
        function(data){
            $("div#total_prize_report").html(data);
            
            $(".toggle_all").change(toggle_all_shipped)
            $(".shipping_mark").change(mark_shipped); // mark shipping changes
            $("input#yearly_gift_shipping").click(update_shipping_method); // allow changing the shipping message
        }
    );
}; // end get_total_prizes_ajax

var mark_shipped = function(event){
    var checked = event.target.checked;
    var id = event.target.dataset.id;
    var type = event.target.dataset.type;
    
    var data = {marked: checked, id:id, type:type};
    
    $.post("../ajax/reports/shipping_mark.php", data, function(data){
        data = JSON.parse(data);
        if (!data.success) {
            event.target.checked = !event.target.checked;
            alert("Could not update shipping status, please contact support");
        }
    });
};

var update_shipping_method = function(event){
    var school_id = event.target.dataset.school_id;
    var shipping_method = $("input:radio[name='yearly_gift_shipping_"+school_id+"']:checked").val();
    
    var data = {shipping_method: shipping_method, school_id: school_id};
    
    $.post("../ajax/reports/shipping_method.php", data, function(data){
        data = JSON.parse(data);
        if (!data.success) {
            alert("There was an error updating the shipping method. Please contact support with further details");
        }
    });
};

var print_form = function(event){
    event.preventDefault(); // prevent the link from working
    // get the id
    var school_id = $("select#school_id").val();    var start_date = $("input#start_date").val();
    var end_date = $("input#end_date").val();       var filter = $("select#filter").val();
    // has the params
    params = $.param({
        school_id: school_id,
        start_date: start_date,
        end_date: end_date,
        filter: filter
    });
    // build the url
    var url = event.target.href + "?" + params;
    // open in new tab: https://stackoverflow.com/questions/4907843/open-a-url-in-a-new-tab-and-not-a-new-window-using-javascript
    var win = window.open(url, '_blank');
    win.focus();
};

var toggle_all_shipped = function(event) {
    var type = event.target.dataset.type;
    var checked = event.target.checked;
    var school_id = event.target.dataset.school_id;
    
    //console.log([type, checked, school_id]);
    
    $.each($("#table_" + type + "_" + school_id + " .shipping_mark"), function(index, item){
        if(item.checked != checked) { // make sure that we need to update the item
            item.checked = checked; // then update it
            $(item).change(); // run the persisting function
        }
    });
}

var toggle_shipped_whole_page = function(event) {
    if (!confirm("This will mark every student/staff member on the page as shipped. Are you sure you want to do this?")) {
        return false;
    }
    $.each($(".toggle_all"), function(index, toggle){
        toggle.checked = true;
        $(toggle).change();
    });
}
