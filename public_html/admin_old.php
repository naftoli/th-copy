<?
require_once('file_save.php'); 
require_once('calendar.php'); 
require_once('card_printer.php');

if ($admin->school_id > 0 && $admin->camp_id > 0)
    $school_and_camp = true;
elseif ($admin->school_id == 0 && $admin->camp_id > 0)
    header("Location:http://www.mashpia.com/camps/index.php"); 
    
if ($admin->is_parent == true) { 
    if (!isset($_POST["child_id"])) 
        $child_id = 0;      
    else 
        header("Location:admin_parent.php");
}
$admin_school_id = 0;
if (isset($_GET["school_id"])) {
    $_SESSION["school_id"] = $_GET["school_id"];
    $admin_school_id = $_GET["school_id"];
}

$admin_class_id = 0;
if (isset($_GET["class_id"])) {
     $admin_class_id = $_GET["class_id"];
}

if (isset($_POST['personal_info'])) {
    update_admins($admin_user['admin_id']);
}
// update admins record
function update_admins($admin_id) {
    $sql = "UPDATE admins  
            SET 
            first = '" . mysql_real_escape_string($_POST['first'])  . "' ,
            last =  '" . mysql_real_escape_string($_POST['last'])  . "' ,
            admin_address1 = '" . mysql_real_escape_string($_POST['admin_address1'])  . "' ,
            admin_address2 = '" . mysql_real_escape_string($_POST['admin_address2'])  . "' ,
            admin_city = '" . mysql_real_escape_string($_POST['admin_city'])  . "' ,
            admin_state = '" . mysql_real_escape_string($_POST['admin_state'])  . "' ,
            admin_postal = '" . mysql_real_escape_string($_POST['admin_postal'])  . "' ,
            admin_phone_home = '" . mysql_real_escape_string($_POST['admin_phone_home'])  . "' ,
            admin_phone_mobile = '" . mysql_real_escape_string($_POST['admin_phone_mobile'])  . "' ,
            admin_email = '" . mysql_real_escape_string($_POST['admin_email'])  . "', 
            username = '" . mysql_real_escape_string( $_POST['username'] ) . "', 
            password = '" . mysql_real_escape_string( $_POST['password'] ) . "' 
        WHERE admin_id = $admin_id" ;       
    $query = mysql_query($sql); 
    if($query){ 
        header("Location: admin.php");
    }   
    else{
        include('constant_file.php');
        @mail($programmers_email2, 'Error in program register_parent.php',  "error in SQL update statement: " , mysql_error() );        
    }   
}

if ($admin_school_id > 0) 
	    $row = mysql_fetch_assoc(mq("SELECT logo, school_name, inst_name, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, school_logo_id, school_era FROM schools LEFT JOIN institutions USING (inst_id) WHERE school_id=" . $admin_school_id)); 
?>

<!--
<div class="photo">
    <? 
    $p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
    $res = mysql_query($p);
    $pRow = mysql_fetch_assoc($res);
    $photo = $pRow['photo'];
    if (empty($photo)) { ?>
        <form action="upload_photo.php" method="post" enctype="multipart/form-data">
            Upload Personal Picture:<br /><input type="file" name="photo" /><br />
            <input type="hidden" name="admin_id" value="<?=$admin_user['admin_id']?>" />
            <input type="submit" name="submit" value="submit" />
        </form>
    <? 
    } else {
        $size = getimagesize("images/staff/$photo"); 
        $width = $size[0];
        $height = $size[1];
        if ($width > 150) {
            if ($width > 250) {
                if ($width > 450) {
                    $width = 0.25 * $width;
                    $height = 0.25 * $height;
                } else {
                    $width = 0.5 * $width;
                    $height = 0.5 * $height;
                }
            } else {
                $width = 0.75 * $width;
                $height = 0.75 * $height;
            } 
        }
        echo "<img src='images/staff/$photo' width='$width' height='$height' />";
        echo "<br /><a href='#'>update photo</a>";
        echo "<span class='upload'></span>";
    } ?>
</div>

<DIV>
    <?=!is_null($row['school_logo_id']) ? linkImgFile($row['school_logo_id'], NULL, '100') : ''?>
</DIV>

<DIV>
    <?=T_('Welcome, Commanding Officer')?>: <?=$admin_user['display']?>
</DIV>

<H3>
    <?=T_('of')?> <?=es($school_name = $row['school_name'])?>
</H3>

<ADDRESS>
    <?=es($row['school_address1'])?><BR>
    <?=es($row['school_address2'])?><?=$row['school_address2'] ? '<BR>' : ''?>
    <?=es($row['school_city'])?> <?=es($row['school_state'])?>, <?=es($row['school_postal'])?><BR>
    <?=es($row['school_country'])?><?=$row['school_country'] ? '<BR>' : ''?>
    <?=es($row['school_phone'])?><?=$row['school_phone'] ? '<BR>' : ''?>        
</ADDRESS>                
<br />

<? if (!is_null($row['school_era'])) : ?>               
    <div class="infobox">                       
        <?=sprintf(T_('This school is not registered for the current year. Please follow %sthis%s link to register.'), "<a href='registration.php?admin_id=$admin_user[admin_id]&school_id=$schl_id'>", '</a>')?>
    </div>
    
    <HR> 
<? endif; ?>

<div class="wall">
    
    <?
    $sql3 = "select * from users where user_registered > 0 and school_id = " . $school_id;
    $result3 = mysql_query($sql3);
    $sql4 = "select * from users where (user_registered is null or user_registered = 0) and school_id = " . $school_id;
    $result4 = mysql_query($sql4);
    $registered = mysql_num_rows($result3);
    $notRegistered = mysql_num_rows($result4);
    if ($notRegistered) {
    ?>
    <fieldset>
        <legend>Mivtzah Tzivos Hashem</legend>
        <div>
            <span class="red">No Soldier Left Behind!</span><br />
            You have <?=$notRegistered?> soldiers still unregistered!<br />
            Click <a href="admin_users_register_new.php?registered=false">here</a> to register them.<br />
        </div>
    </fieldset>
    <? } ?>
    
    <fieldset>
        <legend>Shabbos Mevarchim Teves World Wide Tehillim Club</legend>
        <p>
            Click <a href="https://www.dropbox.com/s/cghbt9037h7wn61/World%20Wide%20Tehillim%20Club%20Campaign%20Overview.pdf">here</a> for the Tehillim Campaign Overview.
        </p>
        <p>
            View the Tehillim Ladder <a href="https://www.dropbox.com/s/ninxtpfnln2tweq/The%20Tehiilim%20Master%20Quota%20chart%20Ladder%203%20-%207.pdf">here</a>.
        </p>
        <p>
            Click <a href="https://www.dropbox.com/s/e8cehq5jw97do3b/Parents%20Letter.pdf">here</a> to print out parent’s letter (per grade).
        </p>
        <p>
            Adjust your school's tehillim quota <a href="admin_users_track.php">here</a>.
        </p>
        <p>
            .
        </p>
    </fieldset>
    
    <fieldset>
    	<legend>Chof Daled Teves Story CD</legend>
    	<p>
    		Encourage your students to purchase the Chof Daled Teves Story CD <a href="admin_users_register_new.php">here</a>. 
    	</p>
    </fieldset>


    <fieldset>
    	<legend>Hey Teves Video</legend>
    	Click <a href="https://www.dropbox.com/sh/y5uiy1nvomj22vo/I9ZJp3pttq">here</a> 
    	for hey teves video!
    </fieldset>
    <!--
    <fieldset>
        <legend>Chanuka Rally</legend>
        <p>
        	Click <a href="downloads/Chanukah.f4v">HERE</a> to download the Chanukah rally!<br />
            For online streaming, visit <a href="http://chabad.org/rally">chabad.org/rally</a>.
            <!--
            To download full rally click <a href="http://we.tl/sYousJRYcN">here</a>.<br />
            To watch online click <a href="https://vimeo.com/77559051">here</a>.<br /><br />
            To see excerpts click on the following links:<br />
            <a href="https://vimeo.com/77559050">Promotions</a><br />
            <a href="https://vimeo.com/77558519">Order of the Day</a><br />
            <a href="https://vimeo.com/77558096">Raffle Winners</a><br /><br />                            
            To view the rally Sicha click <a href="https://www.dropbox.com/s/3979cb0dnml87fo/Chof%20Cheshvan%20Sicha%20-%20PDF.pdf">here</a>.                          
        </p>
        <p>
            For Rally Instructions click <a href="https://www.dropbox.com/s/p2gp0gyfuohb7p5/rally%20document.pdf">here</a>.
        </p>
        <p>
            Participate in the <a href="medal_rank_ceremony.php">Medal and Rank Ceremony</a>.
        </p>
        <p>
            Decorate your school with our <a href="https://www.dropbox.com/sh/bt591skgtl96gnk/MKs-a07trQ">RALLY POSTER</a>.
        </p>
    </fieldset>

    <fieldset>
        <legend>Resources</legend>
        <p>
            To access the weekly Hachayol and Teacher Resources click <a href="https://www.dropbox.com/sh/25j9r51fkkdwgdr/3RwlMPHr-O">here</a>.
        </p>
        <p>
            To access the Commander's Calendar click <a href="downloads/Commanders Calendars 5774.pdf">here</a>.
        </p>
        <p>
        	To access the Tanya Resources click <a href="https://www.dropbox.com/sh/j3t417piw8jtz4e/t5KYOn7FWg">here</a>.
        </p>
    </fieldset>
   	<!--	                            	
   	<?	
    $school_id = $admin->school_id;			
	include 'get_siddurim_info.php';	
	if ( $admin_user['auth'] != 'super' ) { 		
   	?>
   	<br />
   	<div>
   		<span class="red">New Tzivos Hashem Siddurim</span><br />
   		<? if ( $boys || $girls ) { ?>
       		IYH we will be sending you:<br />
       		<? if ($boys) { ?>
            <?=$boys?> Blue Siddurim for your boys that purchased the Siddur (add-on).<br />
            <? } if ($girls) { ?>
            <?=$girls?> Purple Siddurim for your girls that purchased the Siddur (add-on).<br />
       		<? } ?>
   		<? }
   		if ( $blue || $purple ) {
            if ( $blue && $purple ) {
            	echo "You have " . $blue . " additional Blue siddurim that you have purchased.<br />";
            	echo "You have " . $purple . " additional Purple siddurim that you have purchased.<br />";
            } else {
            	echo "You have " . ($blue ? $blue : $purple) . " additional " . ($blue ? 'blue' : 'purple') . " siddurim that you have purchased.<br />";
			}
		}
		?>
		
   		Click <a href="admin_users_register_new.php">here</a> to purchase the Siddur add-on for more children.<br />
		Click <a href="siddurim.php">here</a> to purchase additional Siddurim.<br />
   		
   		<?
   		require_once 'class.adminSchools.php';
		$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
		$schools = $as->getSchools();
		foreach( $schools as $id => $school ) {
			echo "To see the list of siddurim that will be sent out to " . $school . ", 
			please click <a href='siddurim/$id.xlsx'>here</a>!<br />";
		}
		?>
   	</div>
   	<? } ?>
     
    <?
        //print_r($admin);
        $sql = "select 
                (select count(*) from users
                where school_id = " . $admin->school_id . ") as total, 
                (select count(*) from users  
                where user_registered > 0
                and school_id = " . $admin->school_id . ") as registered, 
                (select count(*) from users 
                where 
                    (user_registered is null or 
                    user_registered = 0) 
                    and school_id = " . $admin->school_id . ") as unregistered 
                from users";
        //echo $sql;
        $res = mysql_query($sql);
        $reg = mysql_fetch_assoc($res);
		
		$totalTanya = 0;
		$totalMishna = 0;
		$sqlTanya = "select total_tanya, total_mishna from tanya_totals where school_id = " . $admin->school_id;
        $resultTanya = mysql_query( $sqlTanya );
        if ( mysql_num_rows($resultTanya) > 0 ) {
            $rowTanya = mysql_fetch_assoc($resultTanya);
            $totalTanya = $rowTanya['total_tanya'];
            $totalMishna = $rowTanya['total_mishna'];
        }
    ?>
    <br />  
                  
    <div>
    	<b>Enter your schools present for the Rebbe 111th Birthday</b><br />
    	<span class="red">You can <a href="http://mashpia.com/admin_users_tanya_total.php?school_id=<?=$admin->school_id?>">edit</a> the numbers until the deadline</span><br />
		[<?=$totalMishna?>] Total Lines of <b>Mishnayos</b><br />
		[<?=$totalTanya?>] Total Lines of <b>Tanya</b><br />
		Deadline to be included in the <b>slide show</b> at the rally is <b>Zayin Nissan 12pm</b> EST<br />
		Deadline to be included in the <b>present</b> to the Rebbe <b>Yud Nissan 7am</b> EST<br />
		If you would like to give a <b>Duch</b> to the Rebbe with the amounts that each child learned 
		<a href="admin_users_tanya.php?school_id=<?=$admin->school_id?>">Click here</a><br />
		Deadline <b>Yud Nissan 7am</b> EST<br />  
    </div>
    <br />

    <div>   
        <span class="red"><b>Shabbos Mevorchim Nissan – Chodesh Hageula!</b></span><br />  
        Click <a href="http://myshliach.com/media/pdf/724/WllT7246858.pdf">here</a> for Shabbos Mevorchim Nisan Quota Charts<br />
        Click <a href="http://mashpia.com/shabbos_mevorchim.php">here</a> for Shabbos Mevorchim Army Wide Report<br />
        Click <a href="http://mashpia.com/shabbos_mevorchim_summary.php">here</a> for Shabbos Mevorchim Class Report
    </div>
    <br />
    
    <div>    
        <span class="red"><b>The Rebbe's Birthday present</b></span><br />
        <b><?=$daysLeft?></b> days left to Yud aleph Nissan<br />
        <br />
        Click <a href="https://www.dropbox.com/s/kfyn84g3prmyxz6/Yud%20Aleph%20Nissan%20countdown%20posters.pdf">here</a> for countdown signs to hang up in school each day until Yud Aleph Nissan.<br />
        How many lines of Tanya will your base be giving?<br />
        How many lines of Mishnayus will your base be giving?<br />
        We are relying you to ensure that each and every Chayol in school learns the most Tanya and Mishnayos they can.<br />
        <br />
        Are all the children enrolled?<br />
        Do they all have a quota?<br />
        Are you tested your chayolim and marked their lines up to date?<br />
        Have you removed any missions that are overdue and will not be completed?<br />
        <br />
        ON WEDNESDAY Beis Nissan - the 13th of March at 5pm, any missions that are overdue 
        will be removed so that the reports can reflect the actual amounts memorized.
    </div>
    <br />
</div>
-->

    <? $first = true; ?>
	<? $cntr = 0; ?>
	<? $found = false; ?>
	
	<!-- ********** CAMPS ********** -->
	<? if (!empty($admin_user['auths']['camp']) || $school_and_camp == true) : ?>
	
	    <? include('admin_inc.php');  ?>                        
	
	    <? foreach ($admin_user['auths']['camp'] as $camp_id) : ?>
	        <input type="hidden" name="CAMP ID" value="<?=$camp_id;?>">
	    <? endforeach; ?>
	    
	<? endif; ?>
	<!-- ********** CAMPS ********** -->

	<? if (($cntr == 0 || $school_id == $admin_school_id) && ($found == false)) : ?>
	
		<?
		$cntr++;
		$found = true;
	
	    unset($report_id);
	    
	    if (($action = gr('action')) && gri('todo_school_id') == $school_id) {
	        	
	        switch($action) {
	        
	            case 'report_print':
	                if(!isset($do)) $do = 'print_date = NOW()';
	                
	            case 'report_unprint':
	                if(!isset($do)) $do = 'print_date = NULL';
	                
	            case 'report_processed':
	                if(!isset($do)) $do = 'process_date = NOW()';
	                
	            case 'report_unprocess':
	                if(!isset($do)) $do = 'process_date = NULL';
	                $report_id = gri('report_id', -1);
	                if (mysql_result(mq("SELECT COUNT(*) FROM reports WHERE report_id = $report_id"), 0))
	                    mq("INSERT INTO report_marks SET report_id = $report_id, auth = 'school', id = $school_id, $do ON DUPLICATE KEY UPDATE $do");
	                unset($do);
	            break;
	
	            case 'todo_mark':
	                $todo_id = gri('todo_id', -1);
	                if (mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
	                    mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'school', id = $school_id");
	            break;
	
	            case 'todo_unmark':
	                $todo_id = gri('todo_id', -1);
	                if (mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
	                    mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'school' AND id = $school_id");
	            break;
	        }
	    }
	?>
	
	<? if (1==2) : ?>               
	<? else : ?>
	    <? if ($admin_user['auth'] == 'inactive') : // fixme: loop for each school, or class, or user, etc ?>                   
	        <H2>Base Management</H2>
	
	        <P>
	            View your <A HREF="admin_school.php" style="background-color: lightblue;">Base Profile</A> to upload your school logo, school database, or edit the information about your school.
	        </P>
	        <P>
	            You will receive an e-mail when your account activation is complete.
	        </P>
	        <P>
	            Please proceed to the To-Do list below:
	        </P>
	        <P>
	            Thank you and much Hatzlocho!
	        </P>
	        
	        <HR>
	    <? endif; ?> <!-- if (!is_null($row['school_era'])) -->
	                
	<? if (1==2) : //remove todo list as per shimmy's request 10/21/10 ?>               
	<H2 id="todo_list_school_<?=$school_id?>">
	    <?=T_('My To-Do list');?>
	</H2>
	
	<? $view_all = gri('view_all', 0); ?>
	
	<P>
	    <A HREF="admin.php?view_all=<?=!$view_all?>#todo_list_school_<?=$school_id?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
	</P>
	
	<TABLE class="list list_<?=$align_start?>">
	    <THEAD>
	        <TR>
	            <TH><?=T_('Priority')?></TH>
	            <TH><?=T_('Due Date')?></TH>
	            <TH><?=T_('Description')?></TH>
	            <TH><?=T_('View/Print')?></TH>
	            <TH><?=T_('Complete')?></TH>
	        </TR>
	    </THEAD>
	
	    <? if ($admin_user['auth'] != 'inactive') : ?>
	
	        <? $result = mq("SELECT reports.report_id, report_name, report_type, start_date, end_date, print_date, process_date, visibility FROM reports LEFT JOIN report_marks ON (reports.report_id = report_marks.report_id AND id = $school_id AND auth = 'school') WHERE visibility != 'none'" . ($view_all ? '' : " AND ((print_date IS NULL AND visibility != 'process') OR process_date IS NULL)") . ' ORDER BY creation_date, report_name, reports.report_id'); ?>
	
	    <TBODY>
	        <TR>
	            <TH colspan="4"><A HREF="#cat_school_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" onClick="var el = document.getElementById('cat_school_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=isset($report_id) ? '&minus;' : '+'?></SPAN> &lt;<?=T_('Reports')?>&gt;</A></TH>
	            <TH><?=sprintf(T_('%d items'), mysql_num_rows($result))?></TH>
	        </TR>
	    </TBODY>
	<? endif; ?>
	<TBODY id="cat_school_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" style="<?=!isset($report_id) ? 'display: none;' : ''?> border-top: none;">
	    <? while($row=mysql_fetch_assoc($result)) : ?>
	
	        <TR>
	            <TD></TD>
	            <TD></TD>
	            <TD><?=es($row['report_name'])?></TD>
	            <TD>
	            
	            <? if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : ?>
	                <? if ($row['visibility'] != 'process') : ?>
	                    <A HREF="<?=reportTypeURL_view($row['report_type'], 'school', $school_id, $row['start_date'], $row['end_date'])?>"><?=T_('View and print Report')?>&raquo;</A>
	                <? else: ?>
	                    <?=T_('Not available for printing')?>
	                <? endif; ?> <!-- if ($row['visibility'] != 'process') : -->
	                    <BR>
	            <? endif; ?> <!-- if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : -->
	            
	            <? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
	                <A HREF="<?=reportTypeURL_mark($row['report_type'], 'school', $school_id, $row['start_date'], $row['end_date'])?>"><?=T_('Process Report')?>&raquo;</A>
	            <? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') :  -->
	    
	    </TD>
	    
	    <TD>
	        <? if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) : ?>
	            <?=is_null($row['print_date']) ? "<A HREF='admin.php?action=report_print&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list_school_$school_id'>" . T_('Mark as printed') . '&raquo;</A>' : T_('Printed on') . " {$row['print_date']}<BR><A HREF='admin.php?action=report_unprint&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list_school_$school_id'>" . T_('Unmark as printed') . '&raquo;</A>' ?>
	            <BR>
	        <? endif; ?> <!-- if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) : -->
	        
	        <? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
	            <?=is_null($row['process_date']) ? "<A HREF='admin.php?action=report_processed&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list_school_$school_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Processed on') . " {$row['process_date']}<BR><A HREF='admin.php?action=report_unprocess&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list_school_$school_id'>" . T_('Unmark as done') . '&raquo;</A>' ?>
	        <? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : -->
	    </TD>
	    
	</TR>
	
	<? endwhile; ?> <!-- while($row=mysql_fetch_assoc($result)) : -->
	</TBODY>
	
	<? endif; ?> <!-- if ($admin_user['auth'] != 'inactive') : -->
					
	<?
	//$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, category_id, subject_name, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM todo_list LEFT JOIN todo_categories USING (category_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'school' AND todo_list_marks.id = $school_id) WHERE (subject_id IN (SELECT subject_id FROM school_subjects WHERE school_id = $school_id) OR subject_id IS NULL) AND visibility != 'none' AND todo_list.school_id IS NULL AND todo_list.recip = 'school' AND (todo_list.recip_id = $school_id OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY subject_name, category_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');
	
	$row = $old_row = mysql_fetch_assoc($result);
	
	if ($row) do {
	  $count = 0;
	  ob_start();
	
	  do {
	    $count++;
	    if (isset($todo_id) && $row['todo_id'] == $todo_id) $this_todo = true;
	    $old_row = $row;
	    $cat = $row['category_id'];
	?>
	<TR>
	<TD><?=$row['todo_priority']?></TD>
	<TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
	<TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
	<TD>
	
	    <? if (!is_null($row['todo_file_id'])) : ?>
	        <A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d">
	            <?=T_('View/Print File')?>&raquo;
	        </A>
	    <? endif; ?> <!-- if (!is_null($row['todo_file_id'])) : -->
	    
	    <? if ($row['todo_url']) : ?>
	        <A HREF="<?=es($row['todo_url'])?>">
	            <?=T_('Goto Link')?>&raquo;
	        </A>
	    <? endif; ?> <!-- if ($row['todo_url']) : -->
	    
	</TD>
	<TD><?=is_null($row['mark_date']) ? "<A HREF='admin.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;todo_school_id=$school_id&amp;cat=$cat#todo_list_school_$school_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='admin.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;todo_school_id=$school_id&amp;cat=$cat#todo_list_school_$school_id'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
	</TR>
	<?
	    $row = mysql_fetch_assoc($result);
	  } while($row && $row['category_id'] == $old_row['category_id']);
	?>
	<? $out = ob_get_clean(); ?>
	<TBODY>
	  <TR>
	    <TH colspan="4"><A HREF="#cat_school_<?=$school_id, '_', $old_row['category_id']?>" onClick="var el = document.getElementById('cat_school_<?=$school_id, '_', $old_row['category_id']?>'); if (el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=$old_row['category_id'] == gr('cat') ? '&minus;' : '+'?></SPAN> <?=es($old_row['subject_name']), ' / ', es($old_row['category_name'])?></A></TH>
	    <TH><?=sprintf(T_('%d items'), $count)?></TH>
	  </TR>
	</TBODY>
	<TBODY id="cat_school_<?=$school_id, '_', $old_row['category_id']?>" style="<?=$old_row['category_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
	<?=$out?>
	</TBODY>
	<?
	} while($row);
	unset($out);
	?>
	
	</TABLE>
	
	<? if (isset($s)) : ?>
	    <P>* <?=T_('This Todo is for your school only.')?></P><?unset($s);?>
	<? endif; ?> <!-- if (isset($s)) : -->
	
	<HR>
	
	<? if (0) : // if($admin_user['auth'] != 'inactive'): ?>
	
	
	        <H2><?=T_('My Management system')?></H2>
	
	        <? $menu_type = 'school'; ?>
	        
	        <? include('fore'); ?>
	
	        <HR>
	
	        <H2><?=T_('Tzivos Hashem Stats')?></H2>
	
	        <TABLE class="pretty_grid points">
	            <THEAD>
	                <TR>
	                    <TH></TH>
	                    <TH><?=T_('Soldiers')?></TH>
	                    <TH><?=T_('Total Points')?></TH>
	                    <TH><?=T_('Average Points')?></TH>
	                    <TH><?=T_('Tanya Lines Done')?></TH>
	                    <TH><?=T_('Tanya Pledges')?></TH>
	                    <TH><?=T_('Tanya Collected')?></TH>
	                </TR>
	            </THEAD>
	            <? 
	                $result = mq("
	SELECT 0 ord, '" . T_('All schools') . "' name, (SELECT COUNT(*) FROM users WHERE user_start_date IS NOT NULL) num, (" . totalMarks('JOIN users USING (user_id)') . ") points, (SELECT IFNULL(SUM(lines_done), 0) FROM tanya_users) lines_done, (SELECT IFNULL(SUM(pledges), 0) FROM tanya_users) pledges, (SELECT IFNULL(SUM(collected), 0) FROM tanya_users) collected
	UNION ALL
	SELECT 1 ord, " . ms($school_name) . " name, (SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL) num, (" . totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL") . ") points, (SELECT IFNULL(SUM(lines_done), 0) FROM tanya_users JOIN users USING (user_id) WHERE school_id = $school_id) lines_done, (SELECT IFNULL(SUM(pledges), 0) FROM tanya_users JOIN users USING (user_id) WHERE school_id = $school_id) pledges, (SELECT IFNULL(SUM(collected), 0) FROM tanya_users JOIN users USING (user_id) WHERE school_id = $school_id) collected
	UNION ALL
	SELECT 2 ord, IFNULL(CONCAT('" . T_('Platoon') . ": ', class_grade, '-', class_sub), '". T_('Not in a platoon') . "') name, COUNT(DISTINCT users.user_id) num, IFNULL(marks.mark_points, 0) points, IFNULL(lines_done, 0) lines_done, IFNULL(pledges, 0) pledges, IFNULL(collected, 0) collected FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (" . totalMarks("JOIN users USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL", 'school_id, class_id') . ") marks USING (school_id, class_id) LEFT JOIN (SELECT SUM(lines_done) lines_done, SUM(pledges) pledges, SUM(collected) collected, school_id, class_id FROM tanya_users JOIN users USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id GROUP BY school_id, class_id) tanya USING (school_id, class_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL GROUP BY classes.school_id, classes.class_id, mark_points
	UNION ALL
	SELECT 3 ord, IFNULL(CONCAT('" . T_('Squad') . ": ', team_name), '". T_('Not in a squad') . "') name, COUNT(DISTINCT users.user_id) num, IFNULL(marks.mark_points, 0) points, IFNULL(lines_done, 0) lines_done, IFNULL(pledges, 0) pledges, IFNULL(collected, 0) collected FROM users LEFT JOIN teams USING (school_id, team_id) LEFT JOIN (" . totalMarks("JOIN users USING (user_id) LEFT JOIN teams USING (school_id, team_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL", 'school_id, team_id') . ") marks USING (school_id, team_id) LEFT JOIN (SELECT SUM(lines_done) lines_done, SUM(pledges) pledges, SUM(collected) collected, school_id, team_id FROM tanya_users JOIN users USING (user_id) LEFT JOIN teams USING (school_id, team_id) WHERE school_id = $school_id GROUP BY school_id, team_id) tanya USING (school_id, team_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL GROUP BY teams.school_id, teams.team_id, mark_points
	ORDER BY ord, name
	"); 
	            ?>
	            
	            <TBODY>
	                <?while($row=mysql_fetch_assoc($result)):?>
	                <TR>
	                    <TH><?=es($row['name'])?></TH>
	                    <TD><?=$row['num']?></TD>
	                    <TD><?=number_format($row['points'], 2)?></TD>
	                    <TD><?=$row['num'] ? number_format($row['points']/$row['num'], 2) : '-'?></TD>
	                    <TD><?=number_format($row['lines_done'], 0)?></TD>
	                    <TD><?=money_format('%n', $row['pledges'])?></TD>
	                    <TD><?=money_format('%n', $row['collected'])?></TD>
	                </TR>
	                <?endwhile;?>
	            </TBODY>
	            
	        </TABLE>
	<? else: ?>
	        <input type='hidden' name='ZERO CHECK' value='NOT ZERO'>
	<? endif; ?> <!-- if (0) : -->
	
	                <? endif; //registered ?>
	                
	                <? $first = false; ?>
	                
	                    <? endif; ?>
	                    
    <? if (count($admin_user['auths']['class']) > 0 && 1 == 2) { ?>
    <? $school_id = $admin_user['auths']['school'][0]; ?>
        <HR>
        <H2><?=T_('My Platoon')?></H2>
        <? $menu_type = 'class'; ?>
        <? include('admin_inc.php'); ?>
    <? } ?>
    
    
<? unset($class_id); ?>

<? $range = gri('range', 0); ?>

<!-- foreach ($admin_user['auths']['user'] as $user_id) -->
<? foreach ($admin_user['auths']['user'] as $user_id) : 

    //-- if ($child_id == $user_id) --//
    if ($child_id > 0 && $child_id == $user_id) :
    
    unset($report_id);
    
    if(($action = gr('action')) && (gri('todo_user_id') == $user_id || gri('code_user_id') == $user_id)) switch($action) {
    
        case 'report_print':
            if(!isset($do)) $do = 'print_date = NOW()';
            
        case 'report_unprint':
            if(!isset($do)) $do = 'print_date = NULL';
            
        case 'report_processed':
            if(!isset($do)) $do = 'process_date = NOW()';
            
        case 'report_unprocess':
            if(!isset($do)) $do = 'process_date = NULL';

            $report_id = gri('report_id', -1);
        
            if(mysql_result(mq("SELECT COUNT(*) FROM reports WHERE report_id = $report_id"), 0))
                mq("INSERT INTO report_marks SET report_id = $report_id, auth = 'user', id = $user_id, $do ON DUPLICATE KEY UPDATE $do");
                
            unset($do);
        break;

        case 'todo_mark':
            $todo_id = gri('todo_id', -1);
            if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
                mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'user', id = $user_id");
        break;

        case 'todo_unmark':
            $todo_id = gri('todo_id', -1);
            if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
                mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'user' AND id = $user_id");
        break;

        case 'del_code':
            mq("DELETE FROM user_codes USING user_codes JOIN admin_auths ON (user_id = id AND auth = 'user') WHERE admin_auths.admin_id = {$admin_user['admin_id']} AND user_id = " . gri('code_user_id', -1) . ' AND code_id = ' .  ms(gr('code_id')) . ' AND code_id_prefix = ' . gri('code_id_prefix', -1) . ' AND user_codes.admin_id = ' . gri('code_admin_id', -1));
        break;
}

if (($codes = gra('code')) && gri('user_id') == $user_id) foreach($codes as $code) {
    mq("INSERT IGNORE INTO user_codes (user_id, code_id, code_id_prefix, admin_id) VALUES ($user_id, " . preg_replace('/\D/', '', substr($code, 1)) . ', ' . intval(substr($code, 0, 1)) . ", {$admin_user['admin_id']})");
    $stored_code = true;
} 
else {
  $stored_code = false;
}
?>

<?=$first ? '<H1>' . T_('Welcome') . '</H1>' . (!empty($message) ? '<H2>' . $message . '</H2>' : '') : '<HR><HR>'?>

<?
if ($child_id > 0) {
    $user_row = mysql_fetch_assoc(mq("SELECT user_id, first, last, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, school_name, school_id, school_settings, class_grade, class_sub, role_name FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id) LEFT JOIN admin_auths ON (user_id = id) LEFT JOIN roles USING (role_id) WHERE admin_id = {$admin_user['admin_id']} AND user_id=" . $child_id)); 
}
else {
    $_SESSION["child_id"] = $user_id;
    $user_row = mysql_fetch_assoc(mq("SELECT user_id, first, last, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, school_name, school_id, school_settings, class_grade, class_sub, role_name FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id) LEFT JOIN admin_auths ON (user_id = id) LEFT JOIN roles USING (role_id) WHERE admin_id = {$admin_user['admin_id']} AND user_id = $user_id")); 
}   
?>



<? $school_settings = explode(',', $user_row['school_settings']); ?>

<H2><?=T_('Welcome')?>, <?=$user_row['role_name'] ? $user_row['role_name'] . ' ' . T_('of') . ' ' . $user_row['first'] : ''?> <?=$admin_user['display']?></H2>

<H3><?=T_('My Soldier')?></H3>

<DIV style="font-size: 150%;"><?=es($user_row['first'] . ' ' . $user_row['last'])?><BR>
<?=es($user_row['school_name']), ': ', $user_row['class_grade'], '-', $user_row['class_sub']?>
</DIV>
<ADDRESS>
<?=es($user_row['user_address1'])?><BR>
<?=es($user_row['user_address2'])?><?=$user_row['user_address2'] ? '<BR>' : ''?>
<?=es($user_row['user_city'])?> <?=es($user_row['user_state'])?>, <?=es($user_row['user_postal'])?><BR>
<?=es($user_row['user_country'])?><?=$user_row['user_country'] ? '<BR>' : ''?>
<?=es($user_row['user_phone'])?><?=$user_row['user_phone'] ? '<BR>' : ''?>
</ADDRESS>

<? if (in_array('home_school', $school_settings) && 1==2): //My todo list - Removed to be placed in Soldier's Profile page by Hirshy 8/23/10 as per Shimmy's request ?>
<? $user_id = $child_id ? $child_id : $user_id; ?>
<H2 id="todo_list_user_<?=$user_id?>"><?=T_('My To-Do list')?></H2>
<? $view_all = gri('view_all', 0); ?>

<P>
<A HREF="admin.php?view_all=<?=!$view_all?>#todo_list_user_<?=$user_id?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
</P>
<TABLE class="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Priority')?></TH>
  <TH><?=T_('Due Date')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('View/Print')?></TH>
  <TH><?=T_('Complete')?></TH>
</TR>
</THEAD>

<? $result = mq("SELECT reports.report_id, report_name, report_type, start_date, end_date, print_date, process_date, visibility FROM reports LEFT JOIN report_marks ON (reports.report_id = report_marks.report_id AND id = $user_id AND auth = 'user') WHERE (report_type = 'WWTC' OR report_type = 'mission_cover_sheet') AND visibility != 'none'" . ($view_all ? '' : " AND ((print_date IS NULL AND visibility != 'process') OR process_date IS NULL)") . ' ORDER BY creation_date, report_name, reports.report_id'); ?>

<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" onClick="var el = document.getElementById('cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=isset($report_id) ? '&minus;' : '+'?></SPAN> &lt;<?=T_('Reports')?>&gt;</A></TH>
    <TH><?=sprintf(T_('%d items'), mysql_num_rows($result))?></TH>
  </TR>
</TBODY>

<TBODY id="cat_user_<?=$user_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" style="<?=!isset($report_id) ? 'display: none;' : ''?> border-top: none;">
<?while($row=mysql_fetch_assoc($result)):?>

<TR>
<TD></TD>
<TD></TD>
<TD><?=es($row['report_name'])?></TD>
<TD>
    <? if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : ?>
        <? if ($row['visibility'] != 'process') : ?>
            <A HREF="<?=reportTypeURL_view($row['report_type'], 'user', $user_id, $row['start_date'], $row['end_date'])?>"><?=T_('View and print Report')?>&raquo;</A>
        <? else: ?>
            <?=T_('Not available for printing')?>
        <? endif; ?> <!-- if ($row['visibility'] != 'process') : -->
        <BR>
  <? endif; ?> <!-- if (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel') : -->
  
    <? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
        <A HREF="<?=reportTypeURL_mark($row['report_type'], 'user', $user_id, $row['start_date'], $row['end_date'])?>">
            <?=T_('Process Report')?>&raquo;
        </A>
    <? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : -->
</TD>
<TD>
    <? if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) : ?>
        <?=is_null($row['print_date']) ? "<A HREF='admin.php?action=report_print&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Mark as printed') . '&raquo;</A>' : T_('Printed on') . " {$row['print_date']}<BR><A HREF='admin.php?action=report_unprint&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Unmark as printed') . '&raquo;</A>' ?>
        <BR>
    <? endif; ?> <!-- if ($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')) :  -->
    <? if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : ?>
        <?=is_null($row['process_date']) ? "<A HREF='admin.php?action=report_processed&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Processed on') . " {$row['process_date']}<BR><A HREF='admin.php?action=report_unprocess&amp;report_id={$row['report_id']}&amp;todo_user_id=$user_id#todo_list_user_$user_id'>" . T_('Unmark as done') . '&raquo;</A>' ?>
    <? endif; ?> <!-- if (!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel') : -->
</TD>
</TR>

<?endwhile;?>
</TBODY>

<?
$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, category_id, subject_name, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM users JOIN todo_list LEFT JOIN todo_categories USING (category_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'user' AND todo_list_marks.id = $user_id) WHERE (subject_id IN (SELECT subject_id FROM user_tracks WHERE user_id = $user_id AND enrolled = 1) OR subject_id IS NULL) AND user_id = $user_id AND visibility != 'none' AND todo_list.school_id = users.school_id AND todo_list.recip = 'user' AND (todo_list.recip_id = $user_id OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY subject_name, category_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');

$row = $old_row = mysql_fetch_assoc($result);

if($row) do {
  $count = 0;
  ob_start();

  do {
    $count++;
    if(isset($todo_id) && $row['todo_id'] == $todo_id) $this_todo = true;
    $old_row = $row;
    $cat = $row['category_id'];
?>
<TR>
<TD><?=$row['todo_priority']?></TD>
<TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
<TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
<TD><?if(!is_null($row['todo_file_id'])):?><A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View/Print File')?>&raquo;</A><?endif;?> <?if($row['todo_url']):?><A HREF="<?=es($row['todo_url'])?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
<TD><?=is_null($row['mark_date']) ? "<A HREF='admin.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list_user_$user_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='admin.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list_user_$user_id'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
</TR>
<?
    $row = mysql_fetch_assoc($result);
  } while($row && $row['category_id'] == $old_row['category_id']);
?>
<? $out = ob_get_clean(); ?>
<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_user_<?=$user_id, '_', $old_row['category_id']?>" onClick="var el = document.getElementById('cat_user_<?=$user_id, '_', $old_row['category_id']?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN><?=$old_row['category_id'] == gr('cat') ? '&minus;' : '+'?></SPAN> <?=es($old_row['subject_name']), ' / ', es($old_row['category_name'])?></A></TH>
    <TH><?=sprintf(T_('%d items'), $count)?></TH>
  </TR>
</TBODY>
<TBODY id="cat_user_<?=$user_id, '_', $old_row['category_id']?>" style="<?=$old_row['category_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
<?=$out?>
</TBODY>
<?
} while($row);
unset($out);
?>

</TABLE>
<?if(isset($s)):?><P>* <?=T_('This Todo is for you only.')?></P><?unset($s);?><?endif;?>
<HR>

<? endif; ?>

<? $menu_type = 'user'; ?>
<? include('admin_inc.php'); ?>

<HR>

<? if (in_array('home_school', $school_settings) && 1==2) : //Award Achievement Cards - Removed by Hirshy 8/23 as per Shimmy's request ?>
    <? if ($stored_code) : ?>
        <P id="grant" style="font-size: 200%; color: brown; text-decoration: underline;"><?=sprintf(T_('Card: %s granted to Soldier.'), implode(', ', $codes))?></P>
    <? endif; ?> <!-- if ($stored_code) : -->

    <H2><?=T_('Award Achievement Cards')?></H2>

    <H3><?=T_('Local (school) achievement cards')?></H3>

    <FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
        <?$template_result = mq("SELECT points_codes_template_id, points, subject_name, left_circle, right_circle, description, series FROM points_codes_templates LEFT JOIN subjects USING (subject_id) WHERE (school_id IS NULL OR school_id = {$user_row['school_id']}) ORDER BY subject_name, left_circle, right_circle, description, points_codes_template_id");?>
        <P>
            <LABEL>
                <?=T_('Select Template')?>: 
                <SELECT name="points_codes_template_id">
                    <?while($row = mysql_fetch_assoc($template_result)):?>
                        <OPTION value="<?=$row['points_codes_template_id']?>"><?=floatval($row['points'])?> <?=T_('Miles')?> : (<?=es($row['left_circle'])?>) <?=es($row['description'])?> : (<?=es($row['right_circle'])?>) <?=es($row['subject_name'])?><?=!is_null($row['series']) ? ' #' . $row['series'] : ''?></OPTION>
                    <?endwhile;?>
                </SELECT>
            </LABEL>
            <BR>
            <INPUT type="hidden" name="user_id" value="<?=$user_id?>">
            <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
        </P>
    </FORM>

    <H3><?=T_('Tanya in 5 minutes a day')?></H3>

    <P>
        <?=T_("Once a week print an achievement card for the amount of days your child learnt tanya ba'al peh for 5 minutes.")?>
    </P>

    <FORM action="admin_tanya_cards.php" method="get" accept-charset="UTF-8">
        <P>
            <LABEL>
                <?=T_('Tanya was learned for')?>: 
                    <SELECT name="tanya">
                        <OPTION value="1">1 <?=T_('day')?>, 0.5 <?=T_('miles')?>
                        <OPTION value="2">2 <?=T_('days')?>, 1.0 <?=T_('miles')?>
                        <OPTION value="3">3 <?=T_('days')?>, 1.5 <?=T_('miles')?>
                        <OPTION value="4">4 <?=T_('days')?>, 2.0 <?=T_('miles')?>
                        <OPTION value="5">5 <?=T_('days')?>, 2.5 <?=T_('miles')?>
                        <OPTION value="6">6 <?=T_('days')?>, 3.0 <?=T_('miles')?>
                        <OPTION value="7">7 <?=T_('days')?>, 7.0 <?=T_('miles')?> (<?=T_('Includes bonus')?>)
                    </SELECT>
            </LABEL>
            <BR>
            <INPUT type="hidden" name="user_id" value="<?=$user_id?>">
            <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
        </P>
    </FORM>
    

    <H3><?=T_('Tanya release cards')?></H3>
    

    <P>
        <?=T_('Along the way to earning each medal there are 4 checkpoints when your child will need to be tested on ALL THE TANYA HE KNOWS. Once he hits a check point he will no longer be able to scan more lines. Once you have tested him on EVERYTHING he knows and he can say it perfectly without any mistakes you can award him the tanya release card.')?></P>

        <FORM action="admin_tanya_cards.php" method="get" accept-charset="UTF-8">
            <P>
                <LABEL>
                    <?=T_('Medal stage/progress')?>: 
                    <SELECT name="medal_stage">
                        <OPTION value="1">25%
                        <OPTION value="2">50%
                        <OPTION value="3">75%
                        <OPTION value="4">100%
                    </SELECT>
                </LABEL>
                <BR>
                <INPUT type="hidden" name="user_id" value="<?=$user_id?>">
                <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
            </P>
        </FORM>

        <H3><?=T_('Hakhel Cards')?></H3>

        <P><?=T_('Once you child has completed his hakhel mission for server days you award him a hakhel mission card. If he did 4 out of the six hakhel tasks at least once during the seven days then he gets the bonus card.')?></P>

        <FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
            <P>
                <? $mission_result = mq('SELECT DISTINCT subject_id, subject_name, inst_name, mission_name, mission_number FROM subjects JOIN institutions USING (inst_id) JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE'  . ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '') . ' subject_type != \'school_points\' AND mission_number IS NOT NULL ORDER BY inst_name, subject_name, mission_number, mission_name'); ?>
                <LABEL>
                    <?=T_('Select Mission')?>:
                    <SELECT name="subject_mission">
                        <? while($row = mysql_fetch_assoc($mission_result)): ?>
                        <OPTION VALUE="<?=$row['subject_id']?>/<?=$row['mission_number']?>"><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></OPTION>
                        <? endwhile; ?>
                    </SELECT>
                </LABEL>
                
                <BR>
                
                <LABEL>
                    <?=T_('Cards with Bonus')?>: 
                    <SELECT name="is_bonus" style="width: auto">
                        <OPTION value="0">
                        <OPTION value="1"><?=T_('Yes')?>
                    </SELECT>
                </LABEL>
                
                <BR>
                
                <INPUT type="hidden" name="user_id" value="<?=$user_id?>">
                <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
            </P>
        </FORM>

        <HR>
        
        <H2 id="card_inbox_<?=$user_id?>"><?=T_("View Soldier's Card Inbox")?></H2>
        
        <? $codes = mq("SELECT first, last, code_id, code_id_prefix, user_codes.admin_id, grant_date FROM user_codes LEFT JOIN admins USING (admin_id) WHERE user_id = $user_id ORDER BY grant_date");?>
        
        <? if (!mysql_num_rows($codes)) : ?>
            <P><?=T_('No cards.')?></P>
        <? else: ?>
            <TABLE class="pretty_grid">
                <TR>
                    <TH><?=T_('Granted By')?></TH>
                    <TH><?=T_('Grant Date')?></TH>
                    <TH><?=T_('Barcode #')?></TH>
                    <TH><?=T_('Subject')?></TH>
                    <TH><?=T_('Points + Bonus')?></TH>
                    <TH colspan="3"><?=T_('Description')?></TH>
                    <TH></TH>
                </TR>
                <? while($row = mysql_fetch_assoc($codes)) : ?>
                    <? $code_details = code_details($row['code_id_prefix'], $row['code_id'], $user_id); ?>
                <TR>
                    <TD><?=es($row['first'] . ' ' .$row['last'])?></TD>
                    <TD><?=$row['grant_date']?></TD>
                    <TD><?=$row['code_id_prefix'].str_pad($row['code_id'], 19, '0', STR_PAD_LEFT)?></TD>
                <? if ($code_details) : ?>
                    <TD><?=es($code_details['subject_name'])?><?=!is_null($code_details['series']) ? ' #' . $code_details['series'] : ''?></TD>
                    <TD><?=floatval($code_details['points']) . (floatval($code_details['bonus']) ? ' + ' . floatval($code_details['bonus']) : '')?></TD>
                    <TD style="text-align: center;"><?=es($code_details['left_circle'])?></TD>
                    <TD style="text-align: center;"><?=es($code_details['description'])?></TD>
                    <TD style="text-align: center;"><?=es($code_details['right_circle'])?></TD>
                <? else: ?>
                    <TD colspan="5" style="text-align: center; font-weight: bold;"><?=T_('Card is missing')?></TD>
                <? endif; ?> <!-- if ($code_details) :  -->
                    <TD><A HREF="admin.php?action=del_code&amp;code_user_id=<?=$user_id?>&amp;code_id_prefix=<?=$row['code_id_prefix']?>&amp;code_id=<?=$row['code_id']?>&amp;code_admin_id=<?=$row['admin_id']?>#card_inbox_<?=$user_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete')?></A></TD>
                </TR>
                <? endwhile; ?> <!-- while($row = mysql_fetch_assoc($codes)) : -->
            </TABLE>
        <? endif; ?> <!-- if (!mysql_num_rows($codes)) :  -->
        
        <HR>        
    <? endif; ?> <!-- if (in_array('home_school', $school_settings)) -->
    

    <? if (!is_null($range) && 1==2) : // Miles Statement - Removed to be placed in Soldier's Profile page by Hirshy 8/23/10 as per Shimmy's request?>
            <?
                $user_miles = mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0);
                $result = userStatement($user_id, rangeToDate($range));
                $running_balance = $user_miles;
            ?>
            
            <H2 id="points_<?=$user_id?>"><?=T_("Soldier's Miles Statement")?></H2>
            
            <TABLE class="pretty_grid">
                <? if (mysql_num_rows($result)) : ?>
                <TR>
                    <TH><?=T_('Posting Date')?></TH>
                    <TH><?=T_('Subject')?></TH>
                    <TH><?=T_('Description')?></TH>
                    <TH><?=T_('Points Earned')?></TH>
                    <TH><?=T_('Balance')?></TH>
                </TR>
                
                <? while ($row = mysql_fetch_assoc($result)) : ?>
                <TR>
                    <TD><?=dateToHebrew($row['mark_date'])?></TD>
                    <TD><?=es($row['subject_name'])?><BR><?=es($row['name'])?></TD>
                    <TD><?=es($row['description'])?></TD>
                    <TD style="text-align: right;"><?=floatval($row['points']) ? number_format($row['points'], 2) : '-'?></TD>
                    <TD style="text-align: right;"><?=number_format($running_balance, 2)?></TD>
                </TR>
                <? $running_balance -= $row['points']; ?>
                <? endwhile; ?> <!-- while ($row = mysql_fetch_assoc($result)) : -->
                <? else: ?>
                    <TR>
                        <TD><?=T_('No transactions for the time period selected.')?></TD>
                    </TR>
                <? endif; ?> <!-- if (mysql_num_rows($result)) : -->
            </TABLE>

            <P class="noprint">
                <?=T_('Show')?>:
                <A HREF="admin.php?range=0#points_<?=$user_id?>"><?=T_('Today')?></A> &bull;
                <A HREF="admin.php?range=1#points_<?=$user_id?>"><?=T_('This week')?></A> &bull;
                <A HREF="admin.php?range=2#points_<?=$user_id?>"><?=T_('Two weeks')?></A> &bull;
                <A HREF="admin.php?range=4#points_<?=$user_id?>"><?=T_('Four weeks')?></A> &bull;
                <A HREF="admin.php?range=52#points_<?=$user_id?>"><?=T_('One Year')?></A>
            </P>
    <?endif;?> <!-- if (!is_null($range)) :  -->

    <? $first = false; ?>
    
    <? endif; ?> 
    <!-- if ($child_id == $user_id) -->
    
<? endforeach; ?>
<!-- foreach ($admin_user['auths']['user'] as $user_id) -->
    
<? unset($user_id); ?>

<? //endif; ?>

<? if($admin_user['auth'] != 'inactive'): ?>
<HR>



<HR>

<? endif; ?>

<? if(count($admin_user['auths']['school']) || count($admin_user['auths']['class']) || count($admin_user['auths']['team'])): ?>

<!--
<H2><?=T_('Tzivos Hashem army recruitment video')?>:</H2>

<P>
<A HREF="http://anash.com/th.html"><?=T_('Watch')?></A> &bull; <A HREF="http://www.anash.com/video/Tzivos_Hashem_intro_to_CTH.avi"><?=T_('Download super high quality')?> (1GB)</A> &bull; <A HREF="http://anash.com/THpresentation_one_4000k.wmv"><?=T_('Download high quality')?> (143MB)</A> &bull; <A HREF="http://anash.com/THpresentation_one_340k.wmv"><?=T_('Download low quality')?> (12MB)</A>
</P>

<HR>

<H2><?=T_('Hakhel Slideshow')?></H2>

<A HREF="Hakhel_Slideshow_for_Teachers.pdf"><?=T_('Download Hakhel Slideshow for Teachers')?></A>

<HR>
-->

<? endif; ?> 

<!--
<H2><?=T_('Login to my Tzivos Hashem shop')?></H2>

<FORM action="http://www.tzivoshashem.org/shop/index.php?l=login" method="post">
<P>
<INPUT type="hidden" name="issecure" value="">

<LABEL>Username:<BR>
<INPUT type="text" name="username" size="15">
</LABEL>
<BR>
<LABEL>Password:<BR>
<INPUT type="password" name="password" size="15">
</LABEL>
<BR>
<INPUT type="submit" value="<?=T_('Log In')?>">
</P>

<P>
<A HREF="http://www.tzivoshashem.org/shop/index.php?l=account"><?=T_('Register')?></A> &bull;
<A HREF="http://www.tzivoshashem.org/shop/index.php?l=page_view&amp;p=forgot_password"><?=T_('Forgot Password?')?></A>
</P>

</FORM>

<HR>
-->