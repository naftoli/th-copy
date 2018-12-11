<? 
$admin_auth = array('school');
require('header.php');
require('calendar.php');

$admin_auth = $_GET['admin_auth'];

if ($admin_auth == "super")
{
	$top1 = "85px";
	$top2 = "110px";
}
else
{
	$top1 = "15px";
	$top2 = "50px";
}
	
require("classes/school.php");
$school_id = $_GET['school_id'];
$sql = "SELECT * FROM schools WHERE school_id=" . $school_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$school = new \classes\school($row);

$subject_id = $_GET['subject_id'];

require("classes/subject.php");
$sql = "SELECT * FROM subjects WHERE subject_id=" . $subject_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$subject = new subject($row);

$category_id = 0;
if (isset($_GET['category_id']))
	$category_id = $_GET['category_id'];

require("classes/todo_list.php");
$todo_lists = array();
$sql = "SELECT td.*, tdc.category_name, tdm.mark_date ";
$sql = $sql . "FROM todo_list AS td ";
$sql = $sql . "LEFT JOIN todo_categories AS tdc USING (category_id) ";
$sql = $sql . "LEFT JOIN todo_list_marks AS tdm ON (td.todo_id=tdm.todo_id AND tdm.auth='school' AND tdm.id=" . $school_id. ") ";
$sql = $sql . "WHERE subject_id=" . $subject_id . " ";
$sql = $sql . "AND td.visibility !='none' ";
$sql = $sql . "AND td.school_id IS NULL ";
$sql = $sql . "AND td.recip='school' ";
if ($category_id > 0)
	$sql = $sql . "AND td.category_id=" . $category_id . " ";
$sql = $sql . "AND (td.recip_id=" . $school_id . " OR td.recip_id IS NULL) ";
$sql = $sql . "ORDER BY tdc.category_name, td.todo_priority, td.todo_due_date, td.todo_text, td.creation_date, td.todo_id";

$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query))
{
	$todo_list = new todo_list($row);
	$todo_list->set_category_name($row['category_name']);
	$todo_list->set_mark_date($row['mark_date']);
	array_push($todo_lists, $todo_list);
}
?>

							<? if ($admin_auth == "super") : ?>
							<DIV style="position:absolute; top:<?=$top1;?>; left:250px;"><?=str_replace(" ", "&nbsp;", $subject->subject_name);?>&nbsp;-&nbsp;To&nbsp;Do&nbsp;List</DIV>
							<? else : ?>
							<DIV style="position:absolute; top:<?=$top1;?>; left:200px;"><?=str_replace(" ", "&nbsp;", $school->school_name);?>&nbsp;-&nbsp;<?=str_replace(" ", "&nbsp;", $subject->subject_name);?>&nbsp;-&nbsp;To&nbsp;Do&nbsp;List</DIV>
							<? endif; ?>
							
							<TABLE name="todo_table" id="todo_table" style="position:absolute; top:<?=$top2;?>; border:1px solid; width:700px;">
							
								<thead>
									<tr>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">Category</th>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">Priority</th>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">Due&nbsp;Date</th>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">Description</th>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">View/Print</th>
										<th style="border:1px solid #B0ACA2; background-color:#FDF8EA; font-size:12px;">Complete</th>
									</tr>
								</thead>	
								
								<? foreach ($todo_lists as $todo_list) : ?>
								<tr>									
									<td style="border:1px solid #B0ACA2; font-size:12px;"><?=$todo_list->category_name;?></td>
									<td style="border:1px solid #B0ACA2; font-size:12px;"><?=$todo_list->todo_priority;?></td>
									<td style="border:1px solid #B0ACA2; font-size:12px;"><?=str_replace(" ", "&nbsp;", dateToHebrew($todo_list->todo_due_date));?></td>
									
									<td style="border:1px solid #B0ACA2; font-size:12px;">
									<? if (is_null($todo_list->recip_id)) : ?>
										<?=$todo_list->todo_text;?>
									<? endif;?>
									</td>
									
									<td style="border:1px solid #B0ACA2; font-size:12px;">
										<? if (!is_null($todo_list->todo_file_id)) : ?>
										<A HREF="file_view.php?id=<?=$todo_list->todo_file_id?>&amp;m=d">
											<?=T_('View/Print File')?>&raquo;
										</A>
										<? endif; ?> 
										<? if ($todo_list->todo_url) : ?>
											<A HREF="<?=es($todo_list->todo_url)?>">
												<?=T_('Goto Link')?>&raquo;
											</A>
										<? endif; ?>									
									</td>
									
									<td style="border:1px solid #B0ACA2; font-size:12px;" name="mark_td" id="mark_td">
										<? if (is_null($todo_list->mark_date)) : ?>
										<A href="#" name="mark" id="mark" action="mark" data="<?=$todo_list->todo_id;?>">
											Mark as done
										</A>
										<? else : ?>
										<A href="#" name="mark" id="mark" action="unmark" data="<?=$todo_list->todo_id;?>">
											Un-Mark as done
										</A>										
										<? endif; ?>									
									</td>
								</tr>
								<? endforeach; ?>

							</TABLE>
							<? if ($subject_id == 12) { ?>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p>&nbsp;</p>
							<p><a href="maos_chitim.php">Ma'os Chitim Goal Report</a></p>
							<? 
							$url = $_SERVER['HTTP_REFERER'];
							$arr = parse_url($url);
							//print_r ($arr);
							
							?>
							<p><a href="admin_users_tanya.php?<?=$arr['query']?>&mode=pledge">Enter Pledges and Collections</a></p>
							<? } ?>
							
							<? echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n"; ?>
							
							
