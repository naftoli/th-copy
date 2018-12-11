<?php include("includes/header.php"); ?>

<script src="http://www.google.com/jsapi"></script>
<script>
	// Load jQuery
	  google.load("jquery", "1.3.2");
</script>
<script type="text/javascript" src="scripts/easySlider1.7.js"></script>
<script type="text/javascript">
	$(document).ready(function(){	
		$("#slider").easySlider({
			numeric: true, 
			controlsBefore:	'<div class="page_dots">',
			controlsAfter:	'</div>'	
		});
	});	
</script>
<script type="text/javascript">
function ToggleCheckBox(id) {
	var oCk = document.forms['camp_submit'].elements['Check-' + id];
	var oCkLi = document.getElementById("liCheck-" + id);
	if (oCk.checked) {
		oCk.checked = false;
		oCkLi.className = "";
	} else {
		oCk.checked = true;
		oCkLi.className = "checked";
	}
		var x=0;
	for (var i=0; i<document.forms['camp_submit'].length; i++) {
		if (document.forms['camp_submit'].elements[i].checked == true) {x++};
	} 
	if (x==i) {
		document.getElementById("button_register").className += " show";
	}
}
</script>


<body class="blue">

    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Deposit</div>
            <div class="three_column padding_top">
              <div class="content ">
                    <div id="slider">
                      <ul>
                            <li>
                                <form name="camp_submit" action="" method="post">
                                    <div class="checkboxes">
                                        <div id="liCheck-1"><a href="#" onClick="ToggleCheckBox('1'); return false"></a><input type="checkbox" name="Check-1" value="1">I commit to say Tehilim each and every Shabbos Mevarchim.</div>
                                        <div id="liCheck-2"><a href="#" onClick="ToggleCheckBox('2'); return false"></a><input type="checkbox" name="Check-2" value="2">I know that the Rebbe said that this is so
important it affects 3 generations.</div>
                                        <div id="liCheck-3"><a href="#" onClick="ToggleCheckBox('3'); return false"></a><input type="checkbox" name="Check-3" value="3">I will do my very best to do my Tehilim quota
and my time quota and maybe even more.</div>
                                    </div>
                                    <div id="button_register" class="button button_icons">
                                        <div><a href="#" onClick="this.form.submit">Register!</a></div>
                                    </div>
                                </form>
                            </li>
                            <li></li>
                      </ul>
                    </div>
              </div>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>
</body>

<?php include("includes/footer.php"); ?>
