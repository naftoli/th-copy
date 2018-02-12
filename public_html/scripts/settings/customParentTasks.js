// object to hande the custom parent tasks settings
var customParentTasks = function(){
    
    function render_table_row(name, type, id, allow, print) {
        var html = "<tr>";
        html    += "<td>"+name+"</td>";
        html    += "<td><label class='fancy-check-container'>" +
                        "<input class='custom_tasks' type='checkbox' "+(allow ? "checked" : "")+" data-level='"+type+"' data-"+type+"_id='"+id+"'/>"+
                        "<span class='fancy-check'></span></label></td>";
        html    += "<td><label class='fancy-check-container'>" +
                        "<input class='print_custom_tasks' type='checkbox' "+(print ? "checked" : "")+" data-level='"+type+"' data-"+type+"_id='"+id+"'/>"+
                        "<span class='fancy-check'></span></label></td>";
        html    += "</tr>";
        return html;
    }
    
    function render_table(first_col_name, body) {
        var html = "<table class='parents'><thead><tr>"; // start table
        html    += "<th>"+first_col_name+"</th><th>Allow Custom Parent Tasks</th><th>Print Custom Parent Tasks</th>"; // define headings
        html    += "</tr></thead><tbody>"; // start body
        html    += body;
        html    += "</tbody></table>"; // end table body
        return html;
    }
    
    function loadPlatoons(school_id) {
        $.get("//mashpia.com/ajax/getClasses.php?id="+school_id+"&named=true&extra=parent_tasks", function(raw_response){
            plattons = $.parseJSON(raw_response);
            
            var html = "";
            for (var i = 0; i < plattons.length; i++) {
                var platton = plattons[i];
                html += render_table_row(platton.class_name, "class", platton.class_id, platton.parent_tasks.allow, platton.parent_tasks.print);
            }
            html = render_table("Platton", html);
            
            $("#platoonCustomParentTasks > div").html(html);
            
            setupListeners();
        });
    }
    
    function loadStudents(school_id) {
        $.get("//mashpia.com/ajax/getUsersInSchool.php?id="+school_id+"&extra=parent_tasks", function(raw_response){
            students = $.parseJSON(raw_response);
            
            var html = "";
            for (var i = 0; i < students.length; i++) {
                var student = students[i];
                html += render_table_row(student.name, "user", student.user_id, student.allow_parent_tasks, student.print_parent_tasks);
            }
            html = render_table("Name", html);
            $("#studentCustomParentTasks > div").html(html);
            
            setupListeners();
        });
    }
    
    function setupListeners() {
        $(".custom_tasks").change(updateAllowParentTasks);
		$(".print_custom_tasks").change(updateAllowParentTaskPrinting);
    }
    
    function updateAllowParentTasks(event){
        update_custom_parent_task(event, "allow_parent_tasks");
        
        // toggle the printing as well...
        var print_toggle = $(event.target).parent().parent().parent().find(".print_custom_tasks");
        if (print_toggle[0].checked !== event.target.checked) {
            print_toggle[0].checked = event.target.checked;
            print_toggle.change();
        }
    }
    
    function updateAllowParentTaskPrinting(event) {
        update_custom_parent_task(event, "print_parent_tasks");
    }
    
    function update_custom_parent_task(event, type) {
        event.stopImmediatePropagation(); // prevent hitting the server a bunch of times
        var data = Object.assign({}, event.target.dataset, {type: type, checked: event.target.checked});
        $.post("/ajax/custom_parent_tasks/update.php", data, function(raw_response){
            response = $.parseJSON(raw_response);
            if (!response.success) {
                alert(response.error);
                event.target.checked = !event.target.checked;
            }
        });
    }
    
    return {
        loadPlatoons: loadPlatoons,
        loadStudents: loadStudents,
        setupListeners: setupListeners
    };
}();