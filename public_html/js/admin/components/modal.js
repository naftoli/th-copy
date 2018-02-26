// simple wrapper to show/hide custom modals...

var modal = function(element) {
    
    var modal_element = element;
    
    var show = function(){
        // attempts to pull data from hidden input fields and set them to the fields in the modal
        modal_element.css({"visibility": "visible"});
        modal_element.css({"opacity": "1"});
        
        //modal_element.find(".close").click(hide);
    };
    
    var hide = function() {
        modal_element.css({"opacity": "0"});
        setTimeout(function() {
            modal_element.css({"visibility": "hidden"});
        }, 100);
    };
    
    // public variables returned after the encapslated function call
    return {
        show: show,
        hide: hide
    };
};