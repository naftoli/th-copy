$("#generate").click( generate_report );

function generate_report() {
    var school_id = $("select#school_id").val();
    $("#eligible_table").html( "<div class='loader'></div>" );
    $.post( "ajax/eligibility_report_table.php", { school_id: school_id }, function( table ) {
        $("#eligible_table").html( table );
    });
}