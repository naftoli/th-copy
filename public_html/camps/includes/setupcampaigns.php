			<div class="slider">
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
				<div class="col_title"><span>Getting Started</span></div>
				<div class="col_content">
                    <h1>Setup Campains</h1>
                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>In this step you will install campaigns.</p>
                        	<p>Please choose a camp type from the drop down to load up a selection of appropriate campaigns.</p>
                        	<p>You will then be able to select certain missions and assign them to certain groups.</p>
                        </div>
                    </div>
                    <div class="module" id="module-info">
                    	<h1>Install Campaigns</h1>
                        <div class="module_content">
                            <div class="list campaigns">
                                <ul>
                                    <li>
                                        <span class="icon load"></span>
                                        <span>Load campaigns for:</span>
                                        <select onchange="loadMissions">
                                        	<option disabled="disabled">Choose camp type</option>
                                        	<option value="boyscamp">Boys Camp</option>
                                        	<option value="girlscamp">Girls Camp</option>
                                        </select>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" />
                                                <span class="install"><a href="#" title="Install" class="button checkbox-select"><span class="icon"></span>Install</a></span>
                                                <span class="uninstall"><a href="#" title="Uninstall" class="button checkbox-deselect"><span class="icon"></span>Uninstall</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon bullet"></span>
                                        <span class="label"><span class="label title">Campaign </span>Middos</span>
                                        <span class="label"><span class="label title">Includes </span><span class="label small">Line Up, Clean Up, Breakfast</span></span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" />
                                                <span class="install"><a href="#" title="Install" class="button checkbox-select"><span class="icon"></span>Install</a></span>
                                                <span class="uninstall"><a href="#" title="Uninstall" class="button checkbox-deselect"><span class="icon"></span>Uninstall</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon bullet"></span>
                                        <span class="label"><span class="label title">Campaign </span>Tefilla</span>
                                        <span class="label"><span class="label title">Includes </span><span class="label small">Shachris, Mincha, Maariv</span></span>
                                        <div class="clear"></div>
                                    </li>
                                    <li>
                                        <span class="action">
                                            <span class="checklist">
                                                <input type="checkbox" id="Mission-72" class="checkbox" />
                                                <span class="install"><a href="#" title="Install" class="button checkbox-select"><span class="icon"></span>Install</a></span>
                                                <span class="uninstall"><a href="#" title="Uninstall" class="button checkbox-deselect"><span class="icon"></span>Uninstall</a></span>
                                                <span class="progress">Progress</span>
                                            </span>
                                        </span>
                                        <span class="icon bullet"></span>
                                        <span class="label"><span class="label title">Campaign </span>Tefilla</span>
                                        <span class="label"><span class="label title">Includes </span><span class="label small">Shachris, Mincha, Maariv</span></span>
                                        <div class="clear"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="wizard_nav">
                        <p><a class="button rfloat" href="content.php?output=gettingstarted5">Next</a></p>
                        <br class="clear" />
                    </div>
				</div>
			</div>
