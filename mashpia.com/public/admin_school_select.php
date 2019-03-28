					<!-- ***** SCHOOL ***** -->
					<DIV class="school_list select_box">						
						<a class="prev button">
							<span class="icon"></span>
							<span class="label"></span>
						</a>
						
						<SELECT name="school_id" id="school_id">
							<? foreach ($admin->schools as $school) : ?>
								<? if ( ($get_school_id == $school->school_id) || ($get_school_id == 0 && $admin->school_id == $school->school_id) ) : ?>
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
