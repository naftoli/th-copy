<?
/*if (!isset($_GET['gsCounter'])) {
	$gsCounter = 1;
} else {
	$gsCounter = $_GET['gsCounter'];
}	*/
?>
<script>
/*$(function() {
	$('.slider .slider').before($('.slider .slider .col_content').contents().clone(true)).remove();
	var groupTypesArray = $('.module_content a.link').each(function(i){$(this).attr('href')}).toArray();
});*/
$(function() {
	$('.slider:last .module#module-alerts li h3').nextAll().hide();
	$('.slider:last .module#module-alerts li h3').click(function(){
		$(this).nextAll().slideToggle('fast');
		$(this).parents('li').toggleClass('open');
	});
});
</script>
			<div class="slider">
				<div class="col_title"><span>Getting Started</span></div>
				<div class="col_content">

                    <div class="module" id="module-info">
                        <div class="module_content">
                        	<p>This guide will walk you through all the necessary steps to get you up and running in no time.</p>
                        	<p>You can always edit any options in the control panel.</p>
                        </div>
                    </div>	
                    			
                <div class="module" id="module-alerts">
                        <div class="module_content list_expand">
                            <ul>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Setup Camp Profile</h3>
                                    <p>Begin by ensuring that your CAMP PROFILE is complete.</p>
                                    <p>Make sure that you input your session dates.</p>
                                    <p><a class="link" href="content.php?output=campprofile">Setup Camp Profile</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Setup Groups</h3>
                                    <p>You will need to set up and organize all the groups in your camp.</p>
                                    <p>Groups are categorized in three levels;  Group Types, Divisions and Groups.</p>
                                    <p>Group Types - The different ways you divide the camp, i.e., Bunks and Learning Classes.</p>
                                    <p>Divisions - Each type can then be divided, i.e., Older Division and Younger Division.</p>
                                    <p>Groups - The actual groups, i.e., Bunk Aleph and Rabbi Levi Wolfman.</p>
                                    <p><a class="link" href="content.php?output=grouptypes">Setup Groups</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Setup Campers</h3>
                                    <p>You are now ready to add campers.</p>
                                    <p>Campers must be entered into the  system and then they can be registered for the Summer-5770 Program. Once a  camper is registered, you will be able to place that camper in a group.</p>
                                    <p>TIP: You can  add many campers at  once by using the &lsquo;Upload Camper List' link.</p>
                                    <p><a class="link" href="content.php?output=campers">Setup Campers</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Placing Campers into Groups</h3>
                                    <p>Campers can be placed into groups  for each type of group. For example, each camper is placed in a &lsquo;Bunk&rsquo; and also  in a &lsquo;Learning Class&rsquo;.</p>
<p>There are two ways to do this. You  can view a group and place campers into it. Or you can view a list of campers  and place them into the proper groups.</p>
<p>TIP: There is a list of unassigned campers so you can quickly see which campers still need to be placed into groups.</p>
                                    <p><a class="link" href="content.php?output=grouptypes">Setup Groups</a> or <a class="link" href="content.php?output=campers">Setup Campers</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Setup Staff</h3>
                                    <p>You may now add your staff.</p>
                                    <p><a class="link" href="content.php?output=staff">Setup Staff</a></p>
                                </li>
                              <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Assign Staff to Groups</h3>
                                    <p>You can assign a staff member to multiple groups. I.e., a Division Head can be assigned to multiple bunks. When a staff member signs in, s/he will have permission to access only those bunks.</p>
                                    <p>Every staff member also has a staff type. I.e., Couselor, Waiter, Learning Teacher.</p>
                                    <p>Just like with campers, you can view a group and assign staff to it or you can view a list of staff and assign them to a group.</p>
                                    <p>TIP: There is a list of unassigned staff so you can quickly see which staff still need to be assigned to groups.                                    </p>
                                    <p><a class="link" href="content.php?output=grouptypes">Setup Groups</a> or <a class="link" href="content.php?output=staff">Setup Campers</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Install Campaigns</h3>
                                    <p>At this step you will choose what  areas/themes of the camping experience should be counted for achievement and  prizes.</p>
                                    <p>Each theme is called a campaign.</p>
                                  	<p>The details of the campaign will be  chosen and edited in the next section. But first you need to install the  campaigns you feel are right for your camp.</p>
                                    <p><a class="link" href="content.php?output=grouptypes">Setup Campaigns</a></p>
                                </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                               	  	<h3><span class="icon"></span>Setup Missions</h3>
                                  	<p>Each campaign can have a few missions each with a few tasks. For example the 'Tefillah' campaign can include Shacharis, Mincha, Benching and Maariv. And shacharis can include tasks for coming on time, reading out loud etc.</p>
                                  	<p>When you installed a campaign it loaded up pre-installed missions. Now you need to set who and when they need to be done. (you can also edit the names of the tasks and the point value, or remove a task completely.)</p>
									<p>You can apply an entire mission to a (set of) groups and a set of days, or you can set it per task. For example, coming on time applies to all bunks but reading out loud only applies to the younger division.</p>
                                    <p><a class="link" href="content.php?output=missions">Setup Missions</a></p>
                              </li>
                                <li>
                                    <!--<a class="dismiss" href="#" title="Dismiss Alert">x</a>-->
                                	<h3><span class="icon"></span>Setup Store</h3>
                                    <p>You are now ready to set up the store.</p>
                                    <p>There are some pre-installed prizes in the store. You can edit them, remove them and add your own.</p>
                                    <p>Once you activate a prize it will show up in the Kiosk store and be available for the children to purchase.</p>
									<p><a class="link" href="content.php?output=store">Setup Store</a></p>
                                </li>
                            </ul>
            </div>
                    </div>					
                	<? /* switch ($gsCounter) {
						case 1: 
                     break;
					}*/?>
                    
				</div>
			</div>
