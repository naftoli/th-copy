		 <script>
             $(document).ready(function() {
                $(".checklist input:checked").parent().addClass("selected");
                $(".checklist .checkbox-select").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').addClass("selected");
                        $(this).parents('.checklist').find(":checkbox").attr("checked","checked");
                    }
                );
                $(".checklist .checkbox-deselect").click(
                    function(event) {
                        event.preventDefault();
                        $(this).parents('.checklist').removeClass("selected");
                        $(this).parents('.checklist').find(":checkbox").removeAttr("checked");
                    }
                );
				$(".side_menu ul").tabs(".side_main > ul > li", {effect:'fade'});
				//$("").tabs("> .module_content");
				/*$('.side_main > ul > li h1').click(function() {
						$(this).next().toggle('fast');
						return false;
				}).next().hide().first().show();*/
				$(".side_main .group_type").tabs(".side_main .module", {tabs: 'h1', effect: 'slide', initialIndex: null});
				$('.check_all').click(function() {
					$(this).parent().parent().find('.checklist .checkbox-select').click()
				});
				$('.uncheck_all').click(function() {
					$(this).parent().parent().find('.checklist .checkbox-deselect').click()
				});

            });
        </script>
			<div class="slider">
                <div class="col_title">Choose Groups</div>
                <div class="side_tabs">
                    <div class="side_menu">
                        <ul>
                            <li>Bunks</li>
                            <li>Learning Classes</li>
                            <li>Leagues</li>
                        </ul>
                    </div>
                </div>
				<div class="col_content">
    	<div class="module">
            <div class="module_content side_tabs">
                <div class="list side_main">
                	<ul>
                    	<li class="group_type">
                                <h1>Division 1</h1>
                            <div class="module">
                                <div class="module_content">
                                    <div class="list">
                                        <ul>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Alef</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Beis</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Gimmel</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" checked="checked" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Daled</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <a href="#" class="check_all">Check All</a> / 
                                                <a href="#" class="uncheck_all">Uncheck All</a>
                                                <div class="clear"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                                <h1>Division 2</h1>
                            <div class="module">
                                <div class="module_content">
                                    <div class="list">
                                        <ul>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Hey</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Vov</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Zayin</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Bunk Ches</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <a href="#" class="check_all">Check All</a> / 
                                                <a href="#" class="uncheck_all">Uncheck All</a>
                                                <div class="clear"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                    	<li class="group_type">
                                <h1>Young Boys</h1>
                            <div class="module">
                                <div class="module_content">
                                    <div class="list">
                                        <ul>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 1</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 2</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 3</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 4</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <a href="#" class="check_all">Check All</a> / 
                                                <a href="#" class="uncheck_all">Uncheck All</a>
                                                <div class="clear"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                                <h1>Older Boys</h1>
                            <div class="module">
                                <div class="module_content">
                                    <div class="list">
                                        <ul>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 5</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 6</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 7</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <span class="action">
                                                    <span class="checklist">
                                                        <input type="checkbox" id="Mission-72" class="checkbox" />
                                                        <span class="checkbox_check"><a href="#" title="Check" class="checkbox-select"><span class="icon"></span></a></span>
                                                        <span class="checkbox_uncheck"><a href="#" title="Uncheck" class="checkbox-deselect"><span class="icon"></span></a></span>
                                                    </span>
                                                </span>
                                                <span class="icon bullet"></span>
                                                <span class="label">Learning Class 8</span>
                                                <div class="clear"></div>
                                            </li>
                                            <li>
                                                <a href="#" class="check_all">Check All</a> / 
                                                <a href="#" class="uncheck_all">Uncheck All</a>
                                                <div class="clear"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                	</ul>
                </div>
            </div>
                </div>
            </div>
     
        </div> 
