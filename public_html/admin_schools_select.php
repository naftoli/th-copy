				<? if (count($admin->schools) > 1) : ?>
				
					<!-- ***** SCHOOL ***** -->
					<DIV class="school_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<SELECT name="school_id" id="school_id">
							<? foreach ($admin->schools as $school) : ?>
								<? if ($admin->school_id == $school->school_id) : ?>
								<OPTION SELECTED value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? else : ?>
								<OPTION value="<?=$school->school_id;?>"><?=$school->school_name;?></OPTION>
								<? endif; ?>
							<? endforeach; ?>
						</SELECT>
							
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<!-- ***** SCHOOL ***** -->
										
					<!-- ***** CLASS ***** -->
					<? if (in_array("class_id", $selects)) : ?>
					<DIV class="class_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<SELECT name="class_id" id="class_id">
							<OPTION value="0">All Platoons</OPTION>
						</SELECT>
							
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<? endif; ?>
					<!-- ***** CLASS ***** -->
					
					<!-- ***** USER ***** -->
					<? if (in_array("user_id", $selects)) : ?>
					<DIV class="user_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<SELECT name="user_id" id="user_id">
							<OPTION value="0">All Soldiers</OPTION>
						</SELECT>
							
						<a class="next button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>							
					</DIV>
					<? endif; ?>
					<!-- ***** USER ***** -->
					
				<? endif; ?>