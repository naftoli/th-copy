/*
	Dropdown with Multiple checkbox select with jQuery - May 27, 2013
	(c) 2013 @ElmahdiMahmoud
	license: https://www.opensource.org/licenses/mit-license.php
*/

var shipping_report; // expects this object to exist

$(".dropdown dt a").click(function() {
  $(".dropdown dd ul").slideToggle('fast');
});

$(".dropdown dd ul li a").click(function() {
  $(".dropdown dd ul").hide();
});

function getSelectedValue(id) {
  return $("#" + id).find("dt a span.value").html();
}

$(document).bind('click', function(e) {
  var $clicked = $(e.target);
  if (!$clicked.parents().hasClass("dropdown")) $(".dropdown dd ul").hide();
});

$('.mutliSelect input[type="checkbox"]').click(function() {

  //var title = $(this).closest('.mutliSelect').find('input[type="checkbox"]').val(),
  //  title = $(this).val() + ",";
  var title = $.trim($("#raffle_li_" + $(this).val()).text()) + ", ";
  var id = $(this).val();

  if ($(this).is(':checked')) {
    var html = '<span title="' + title + '">' + title + '</span>';
    $('.multiSel').append(html);
    $(".hida").hide();
    
    // add the id to the array
    shipping_report.selected_raffles.push(id);
  } else {
    $('span[title="' + title + '"]').remove();
    var ret = $(".hida");
    $('.dropdown dt a').append(ret);
    
    // remove the raffle from the array
    remove_selected_raffle(id);
  }
  // if the text box is empty reshow the text
  if ($('.multiSel').text() === "") {
    $(".hida").show();
  }
});

// variables for storing the ids of the selected raffles
shipping_report.selected_raffles = [];

function remove_selected_raffle(id){
  var index = shipping_report.selected_raffles.indexOf(id);
  if (index > -1) {
    shipping_report.selected_raffles.splice(index, 1);
  }
}
