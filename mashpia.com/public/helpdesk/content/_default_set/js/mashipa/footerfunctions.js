// *************
// All Pages
// *************
$("a#mashpia_link").click(function(event){
    var src = localStorage.getItem("mashpia_bug_href_source");
    if (src) {
        event.preventDefault();
        localStorage.removeItem("mashpia_bug_href_source");
        window.location.href = src;
    }
});

if(window.location.href.indexOf("/helpdesk/?p=open") > 0) {
    // load the addtional options tab
    mswDeptLoader();
}
