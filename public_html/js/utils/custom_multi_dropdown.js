/*  Custom MultiSelect Dropdown
 *
 *  Extends $.fn to add .multiDropdown() function
 *
 */

$.fn.multiDropdown = function(placeholder) {
    // render the options
    function render_options(options) {
        var html = ""; // the basic html
        // for each optoin
        for(var i = 0; i < options.length; i++){
            var option = options[i]; // get the option from the array
            // and generate an li for it with a fancy checkbox next to it
            html += '<li>';
            html +=     '<label class="fancy-check-container">';
            html +=         '<input type="checkbox" value="'+option[0]+'" '+(option[2] ? "checked" : "")+'/>';
            html +=         '<span class="fancy-check"></span>';
            html +=     '</label>';
            html +=     option[1]; // show the option text
            html += '</li>';
        }
        return html; // return the generated html
    }
    // update the "placeholder"/selected text
    function updateSelectedText(selected_options_text, dropdown, select){
        if( selected_options_text.length > 0 ){
            // hide the placehoder and make sure that this is showing
            dropdown.find(".multiDropdown-selected").css({"display": "inline-block"});
            dropdown.find(".multiDropdown-hide").css({"display": "none"});
            // and update the text
            dropdown.find(".multiDropdown-selected").text(selected_options_text.join(", ")); // update the text
        } else {
            // show the placeholder
            dropdown.find(".multiDropdown-hide").css({"display": "inline-block"});
            dropdown.find(".multiDropdown-selected").css({"display": "none"});
        }
    }
    
    // get all the options and put them in an array
    var options = [];
    var selected_options_text = [];
    var option_items = this.find("option");
    $.each(option_items, function(index, option) {
        if (option.disabled) {return true;} // skip any disabled options
        options.push([option.value, option.text, option.selected]);
        // add it to the selected_options_text if it is selected
        if (option.selected) {selected_options_text.push(option.text);}
    });
    
    // generate the core dropdown
    var dropdown_html = '<div class="multiDropdown">';
    dropdown_html +=     '<span class="multiDropdown-main">';
    dropdown_html +=         '<a>';
    dropdown_html +=             '<span class="multiDropdown-hide multiDropdown-title">'+placeholder+'</span>';
    dropdown_html +=             '<i class="fa fa-chevron-down"></i>';
    dropdown_html +=             '<span class="multiDropdown-selected multiDropdown-title"></span>';
    dropdown_html +=         '</a>';
    dropdown_html +=     '</span>';
    dropdown_html +=     '<span class="multiDropdown-options">';
    dropdown_html +=         '<ul style="display: none">';
    dropdown_html +=             render_options(options);
    dropdown_html +=         '</ul>';
    dropdown_html +=     '</span>';
    dropdown_html += '</div>';
    // insert the dropdown_html into the page
    $(dropdown_html).insertAfter(this);
    
    // cast to a variable so that we can keep it in scope for event listeners....
    var select = this;
    var dropdown = this.parent().find(".multiDropdown"); // find the newly created dropdown and store it for later use....
    select.css({"display": "none"}); // hide the multi-select box
    
    // update the "placeholer"...
    updateSelectedText(selected_options_text, dropdown);
    
    // add event listeners to the dropdown

    // when a checkbox is selected from the dropdown
    dropdown.find('input').change(function(event){ // when a checkbox is changed....
        var selected_options_text = []; // update the selected options text while we do this
        $.each(select.find("option"), function(index, option){ // go through each option in the select menu
            if (option.disabled) {return true;} // skip any disabled options
            if (option.value === event.target.value) { // if the values are the same
                option.selected = event.target.checked; // sync them up
            } // end if values are the same
            if (option.selected) {
                selected_options_text.push(option.text);
            }
        }); // end for each option
        
        updateSelectedText(selected_options_text, dropdown);
    }); // end event listener for checkboxes....
    
    // toggle the dropdown
    dropdown.find(".multiDropdown-main a").click(function(){
        dropdown.find(".multiDropdown-options ul").slideToggle('fast');
    });
}