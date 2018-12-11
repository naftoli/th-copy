		 <script>
             $(document).ready(function() {
                $(".checklist input:checked").parent().addClass("selected");
                $(".checklist .checkbox-select").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').addClass("selected");
                        $(this).parents('.checklist').find(":checkbox").attr("checked","checked");
                        $(this).parents('li').css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
                        $(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
                    }
                );
                $(".checklist .checkbox-deselect").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').removeClass("selected");
                        $(this).parents('.checklist').find(":checkbox").removeAttr("checked");
                        $(this).parents('li').css({ backgroundColor: '#e19494' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});
                        $(this).parents('li').find('.progress').show().delay(500).fadeOut(500);
                    }
                );
            });
        </script>
			<div class="slider">
				<div class="col_title"><span>Manage Store</span></div>
				<div class="col_content">
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<h1>Store Stats</h1>
                            <ul class="stats">
                            	<li>Activated Prizes<span>53</span></li>
                            	<li>Prizes - Registered<span>41</span></li>
                            	<li>Campers - Non-Registered<span>12</span></li>
                            </ul>
                            <ul class="stats">
                            	<li>Vouchers - Printed<span>53</span></li>
                            	<li>Vouchers - Registered<span>41</span></li>
                            	<li>Vouchers - Non-Registered<span>12</span></li>
                            </ul>
                            <ul class="stats">
                            	<a href="content.php?output=???" class="button">Print and Cash</a>
                            </ul>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="module prizes"> 
                        <h1>Available Prizes</h1>
                        <div class="module_content">
                            <div class="list">
                                <ul>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                <span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
                                                <span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon prize"><img src="images/generic_prize.jpg" height="32" /></span>
                                        <span class="label"><span class="label title">Prize</span>Collection of 10 games</span>
                                        <span class="label points"><span class="label title">Points</span>10</span>
                                        <span class="label points"><span class="label title">Available</span>10</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                <span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
                                                <span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon prize"><img src="images/generic_prize.jpg" height="32" /></span>
                                        <span class="label"><span class="label title">Prize</span>Ipod Nano</span>
                                        <span class="label points"><span class="label title">Points</span>360</span>
                                        <span class="label points"><span class="label title">Available</span>10</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                <span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
                                                <span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon prize"><img src="images/generic_prize.jpg" height="32" /></span>
                                        <span class="label"><span class="label title">Prize</span>Kiddush Becher</span>
                                        <span class="label points"><span class="label title">Points</span>18</span>
                                        <span class="label points"><span class="label title">Available</span>10</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" />
                                                <span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
                                                <span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon prize"><img src="images/generic_prize.jpg" height="32" /></span>
                                        <span class="label"><span class="label title">Prize</span>Pre-loaded iPod</span>
                                        <span class="label points"><span class="label title">Points</span>1000</span>
                                        <span class="label points"><span class="label title">Available</span>10</span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="icon add"></span>
                                        <span class="label"><a href="content.php?output=prizeadd" class="add_new_row overlay">Add Prize</a></span>
                                        <div class="clear"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div> 
