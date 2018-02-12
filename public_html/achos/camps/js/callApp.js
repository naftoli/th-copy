

function setApplicationData(action, params)
{
  $.ajax({
      url: "../../application/php/appInterface.php?action="+action+"&params="+params,
      success: function (data) {
        alert();
      }
      success: function (data) {
        alert();
      }
  }
}