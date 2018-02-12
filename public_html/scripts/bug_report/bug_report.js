// when the page loads
$(document).ready(function(){
    localStorage.removeItem("mashpia_bug_href_source");
    // when a user clicks on the bug-report button
    $("#helpdesk_link").click(function(event){
        event.preventDefault();
        var data = {};
        
        if (event.target.dataset.user_id) {
            data.user_id = event.target.dataset.user_id;
        }
        
        $.post("/ajax/get_user_bug_info.php", data, function(response){
            response = $.parseJSON(response);
            if (response.success) {
                localStorage.setItem("mashpia_bug_username", response.admin.username);
                localStorage.setItem("mashpia_bug_phone", response.admin.admin_phone_mobile);
                localStorage.setItem("mashpia_bug_account_type", response.admin.account_type);
                // user info
                if (data.user_id && response.user && response.user.first) {
                    localStorage.setItem("mashpia_bug_school_name", response.user.school_name);
                    localStorage.setItem("mashpia_bug_child", response.user.first + " " + response.user.last);
                } else if (response.admin.school_name) {
                    localStorage.setItem("mashpia_bug_school_name", response.admin.school_name);
                }
                // front page info
                localStorage.setItem("mashpia_bug_email", response.admin.admin_email);
                localStorage.setItem("mashpia_bug_name", response.admin.first + " " + response.admin.last);
                
                if (event.target.dataset.category) {
                    localStorage.setItem("mashpia_bug_category", event.target.dataset.category);
                }
            }
            // set the other params and redirect the user...
            localStorage.setItem("mashpia_bug_url", window.location.href);
            localStorage.setItem("mashpia_bug_href_source", window.location.href);
            window.location.href = "/helpdesk/?p=open";
        }); // end username get
    }); // end bug-report click
}); // end on document ready

