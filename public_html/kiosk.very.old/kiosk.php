<?php include("includes/header.php"); ?>

<body class="green">
    <div id="wrapper">
        <div id="header">
          <?php include("includes/topbar.php"); ?>
      </div>
        <div id="main">
            <div id="page_title">Welcome</div>
            <div class="two_column">
            	<div class="padding_top padding_left member_info_box">
                	<div class="member_photo">
                        <div class="member_photo_img"><img src="images/file_view.jpg" height="150" /></div>
                        <div class="member_photo_cover"></div>
                        <div class="member_badge">
                            <img src="images/ranks/02-Sergent.png" width="100" height="100" />
                        </div>
                    </div>
                    <div class="member_info">
                    	<ul>
                        	<li class="member_name">Menachem Mendel Finkelstein</li>
                        	<li><label>Rank:</label> <span>Private</span></li>
                        	<li><label>Serial #:</label> <span>7737435</span></li>
                        	<li><label>Platoon:</label> <span>Pre-school 1</span></li>
                        	<li><label>Teacher:</label> <span>Test</span></li>
                        	<li><label>Platoon Average:</label> <span>58.85</span></li>
                        	<li><label>Total Miles:</label> <span>235.40</span></li>
                        </ul>
                    </div>
                    <div class="clear"></div>
                </div>
            	<div class="padding_top padding_left">
                	<div class="scan_card">
                    	<div class="scan_card_inside">
                            Scanning Station
							<script>
								function loadShadow(cardnum) {
									// open a welcome message as soon as the window loads
									Shadowbox.open({
										content:    'cardpop.php?card=' + cardnum.value,
										player:     "iframe",
										width:      770,
										height:     430
									});
									cardnum.value='';
									return false;
								};
                            </script>
                            <form name="scancard" id="scancard" onSubmit="return loadShadow(this.scantext)">
                                <input name="scantext" id="scantext" type="text" autocomplete="off"  />
                            </form>
                          <div class="scan_card_cover"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="one_column">
              <ul class="buttons button_icons">
                    <li><a href="profile.php" class="icon_profile">Profile</a></li>
                    <li><a href="deposit.php" class="icon_deposit">Deposit</a><span class="badge">2</span></li>
                    <li><a href="withdraw.php" class="icon_withdraw">Withdraw</a><span class="badge">2</span></li>
                    <li><a href="campaigns.php" class="icon_campaigns">Campaigns</a></li>
                    <li><a href="#" class="icon_wall">Wall of Honor</a></li>
              </ul>
            </div>
        </div>
        <div id="footer">
			<?php include("includes/bottombar.php"); ?>
      </div>
    </div>

<script type="text/javascript" src="scripts/shadowbox/shadowbox.js"></script>
<script type="text/javascript">
setInterval ( "scanFocus()", 1000 );
var scancard = document.forms.scancard.scantext;
function scanFocus() {
	scancard.focus();
}

Shadowbox.init({
    skipSetup: true,
	players:    ["iframe"],
	initialWidth:320,
	initialHeight:30,
	overlayOpacity:0.8
});

</script>

</body>

<?php include("includes/footer.php"); ?>
