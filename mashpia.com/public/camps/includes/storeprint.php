<?
	if (isset($_GET["print"])) {
 ?>
 Testing
 <table width="100%" border="1" cellpadding="2">
   <tr>
     <td colspan="3">Bunk Alef</td>
   </tr>
   <tr>
     <td>Shimmy Bofinger</td>
     <td>Ipod</td>
     <td>&nbsp;</td>
   </tr>
   <tr>
     <td>Shimmy Bofinger</td>
     <td>Ipod</td>
     <td>&nbsp;</td>
   </tr>
 </table>
<? } else { ?>
			<div class="slider">
				<div class="col_title"><span>Print and Cash</span></div>
				<div class="col_content">
					<div class="module lists forms" id="lists">
						<div class="module_content">
                        	<p>Use this page to print out the list of bought prizes.</p>
                        	<p>By clicking print the system will cash the vouchers in the campers account.</p>
						</div>
					</div>
					<div class="module lists forms" id="lists">
						<div class="module_content">
							<ul>
								<li>
<a href="storeprint.php?print=true" target="_blank" class="inline_link">
                                        <div class="icon"></div>
                                        <div class="name">Print and Cash</div>
                                    </a>
								</li>
							</ul>
						</div>
					</div>
				</div>
</div>
<? } ?>