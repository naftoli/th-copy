<?
require_once 'classes/user.php';
require_once 'classes/user_track.php';
require_once 'classes/school_class.php';
require_once 'class.taskExceptions.php';
require_once 'classes/date_tasks_mission.php';
require_once 'classes/daily_task.php';
require_once 'classes/weekly_task.php';
require_once 'classes/shabbos_task.php';
require_once 'classes/no_label_task.php';
require_once 'classes/task.php';
require_once 'classes/date_tasks_mark.php';

class PersonalizedReport {
	private $start;
	private $end;
	private $users;
	private $subject;
	private $tasks;
	private $report;
	private $signed;
	
	public function __construct($start, $end, $users, $subject = null, $tasks = null) {
		$this->start = $start;
		$this->end = $end;
		$this->users = $users;
		$this->subject = $subject;
		$this->tasks = $tasks;
	}
	
	public function createReport($userInfo, $email = false) {
		$report = array();
		//return array containing all info
		foreach ($this->users as $user_id) { 
			$sql = "select * from users where user_id = " . $user_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$user = new user($row);
			//$user->get_rank();
			//$user->get_school_class();
			if ($this->subject) {
				$user->get_user_tracks($this->subject, $this->start, $this->end, $this->tasks);
				//echo "<pre>"; print_r($user); echo "</pre>";
			} else {
				$user->get_user_tracks(-1, $this->start, $this->end);
			}
			
			//echo "<pre>"; print_r($user); echo "</pre>"; exit;
			
			//get the users tasks
			foreach ($user->user_tracks as $obj) {
				if (empty($obj->daily_tasks) && empty($obj->weekly_tasks) && empty($obj->shabbos_tasks) && empty($obj->no_label_tasks)) continue;
				$report[$user_id][$obj->subject_name] = array();
				foreach ($obj->daily_tasks as $task) {
					if (!isset($report[$user_id][$obj->subject_name][$task->task_name])) {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential'] = 7;
						//get number of times task was done
						$numDone = 0;
						foreach ($task->date_task_marks as $mark) {
							if ($mark->marked) {
								$numDone++;
							}
						}
						$report[$user_id][$obj->subject_name][$task->task_name]['actual'] = $numDone;
					} else {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential'] += 7;
						//get number of times task was done
						$numDone = 0;
						foreach ($task->date_task_marks as $mark) {
							if ($mark->marked) {
								$numDone++;
							}
						}
						$report[$user_id][$obj->subject_name][$task->task_name]['actual'] += $numDone;
					}
				}
				foreach ($obj->weekly_tasks as $task) {
					if (!isset($report[$user_id][$obj->subject_name][$task->task_name])) {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential'] = 1;
						$report[$user_id][$obj->subject_name][$task->task_name]['actual'] = 0;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					} else {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential']++;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					}
				}
				foreach ($obj->shabbos_tasks as $task) {
					if (!isset($report[$user_id][$obj->subject_name][$task->task_name])) {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential'] = 1;
						$report[$user_id][$obj->subject_name][$task->task_name]['actual'] = 0;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					} else {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential']++;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					}
				}
				foreach ($obj->no_label_tasks as $task) {
					if (!isset($report[$user_id][$obj->subject_name][$task->task_name])) {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential'] = 1;
						$report[$user_id][$obj->subject_name][$task->task_name]['actual'] = 0;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					} else {
						$report[$user_id][$obj->subject_name][$task->task_name]['potential']++;
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->task_name]['actual']++;
							}
						}
					}
				}
			}
		}
		
		return $this->showReport($report, $userInfo, $email);
	}

	public function createDetailedReport() {
		$report = array();
		
		//return array containing all info
		foreach ($this->users as $user_id) { 
			$sql = "select * from users where user_id = " . $user_id;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$user = new user($row);
			$user->get_rank();
			$user->get_user_tracks($this->subject, $this->start, $this->end, $this->tasks);
			//echo "<pre>"; print_r($user); echo "</pre>"; exit;
						
			//get the users tasks
			foreach ($user->user_tracks as $obj) {
				if (!empty($obj->daily_tasks)) {
					foreach ($obj->daily_tasks as $task) {
						foreach ($task->date_task_marks as $mark) {  
							$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['daily'][] = $mark->marked ? 1 : 0;
						}
					}
				} else if (!empty($obj->weekly_tasks)) {
					foreach ($obj->weekly_tasks as $task) {
						$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['weekly'] = 0; 
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['weekly'] = 1;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['weekly'] = 1;
							}
						}	
					}				
				} else if (!empty($obj->shabbos_tasks)) {
					foreach ($obj->shabbos_tasks as $task) {
						$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['shabbos'] = 0; 
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['shabbos'] = 1;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['shabbos'] = 1;
							}
						}
					}
				} else if (!empty($obj->no_label_tasks)) {
					foreach ($obj->no_label_tasks as $task) {
						$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['no_label'] = 0; 
						if ($task->date_task_mark->marked) {
							if ($task->quantity > 1) {
								if ($task->date_task_mark->done_qty >= $task->quantity) {
									$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['no_label'] = 1;
								}
							} else {
								$report[$user_id][$obj->subject_name][$task->start_date][$task->task_name]['no_label'] = 1;
							}
						}
					}
				}
			}
		}
		
		return $report;
	}

	private function getRank($user, $type = 'name') {
		$sql = "select r.rank_name, r.rank_image_id from ranks r 
				join rank_marks rm using (rank_ord) 
				where rm.user_id = $user 
				order by rm.rank_ord desc 
				limit 1";
		//echo $sql;
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$rankName = $row['rank_name'];
			$rankImage = $row['rank_image_id'];
		} else {
			$sql = "select rank_name, rank_image_id from ranks where rank_ord = 1";
			//echo $sql;
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$rankName = $row['rank_name'];
			$rankImage = $row['rank_image_id'];
		}
		if ($type == 'name') {
			return $rankName;
		} else if ($type == 'image') {
			return $rankImage;
		}
	}

	private function showReport($report, $userInfo, $email) {
		$subjects = array();
        $stickers = array();
		$sql = "select subject_id, subject_name, subject_image_id from subjects";
		$result = mysql_query( $sql );
		while ($row = mysql_fetch_assoc($result)) {
		    $stickers[$row['subject_name']] = $row['subject_image_id'];
			$subjects[$row['subject_name']] = $row['subject_id'];
		}
		
		$sql = "select name from parshos where start = $this->start or start = " . ($this->end - 6);
		//echo $sql;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$parsha['start'] = $row['name'];
		if (mysql_num_rows($result) == 1) {
			$parsha['end'] = $row['name'];
		} else {
			$row = mysql_fetch_assoc($result);
			$parsha['end'] = $row['name'];
		}
		
		if (empty($report)) {
			echo "There are no users in your selection that have this task.";
			exit;
		}
		
		$html = "
		
		<style>
			table {
                font-size: 12px;
            }
            th, td {
                padding: 6px;
                border: 1px solid black;
                vertical-align: middle;
            }
            td.heading {
            	height: 30px;
            	color: white;
            	background-color: black;
            	font-size: 14px;
            }
            .sticker {
            	text-align: center;
            }
            .sticker img {
            	height: 40px;
            }
            td.total {
            	text-align: center;
            	width: 80px;
            }
            div.info {
            	line-height: 1.4;
            	margin-bottom: 10px;
            }
            @media print {
            	.noPrint {
            		display: none;
            	}
            }
			.top {
				height: 120px;
				text-align: center;
				font-size: 12px;
				padding-bottom: 20px;
			}
			.rank {
				width: 200px;
				float: left;
			}
			.base {
				width: 200px;
				float: right;
			}
			th {
				text-align: center;
			}
		</style>";
			
		$emailed = array();
		foreach ($report as $user => $info) {
			$html .= "<div class='infobox2'>";
			$html .= "<table><tr><td colspan='3' class='top' align='center'>";
				$img = $this->getRank($user, 'image');
				$html .= "<span class='rank'><img src='http://mashpia.com/file_view.php?id=$img' height='100' /><br />";
				$html .= $this->getRank($user) . ' ' . $userInfo[$user]['first'] . ' ' . $userInfo[$user]['last'] . "</span>"; 
				$html .= "<span class='base'><img src='http://mashpia.com/file_view.php?id=" . $userInfo[$user]['user_photo_id'] . "' height='100' /><br />" . 
					$userInfo[$user]['school_name'];
				//if we are coming from parent page, there will be a school_info variable
				if (isset($userInfo[$user]['school_class'])) {
					$obj = $userInfo[$user]['school_class'];
					$html .= ' Platoon: ' . $obj->class_grade;
					if (!empty($obj->class_sub)) {
						$html .= "-" . $obj->class_sub;
					}
				} else {
					$html .= ' Platoon: ' . $userInfo[$user]['class_grade'];
					if (!empty($userInfo[$user]['class_sub'])) {
						$html .= "-" . $userInfo[$user]['class_sub'];
					}
				}
				$html .= "</span>";
				$html .= "<span class='kings'><img src='http://mashpia.com/images/parentIcons/scoreboard.gif' height='130' /></span></tr>";
				
				$html .= "
				<tr>
					<td colspan='3' align='center' style='font-size: 14px'>
						From beginning of Parshas $parsha[start] until the end of Parshas $parsha[end]<br />";
				$html .= date('M d, Y', jdtounix($this->start)) . ' - ' . date('M d, Y', jdtounix($this->end));
				$html .= "&nbsp;&nbsp;&nbsp;";
				$html .= iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->start, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH)) .  
					' - ' . iconv('WINDOWS-1255', 'UTF-8', jdtojewish($this->end, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH));
				$html .= "</td>
				</tr>
				<tr>
					<th>Campaign</th>
					<th>Tasks</th>
					<th>Total Tasks Done / <br />Potential</th>
				</tr>";
				
				$oldSubject = false;
				foreach ($info as $subject => $arr) {
					if (!$oldSubject || $oldSubject != $subject) {
						$html .= "<tr><td class='sticker heading' colspan='3'>";
						switch ($subject) {
							case 'שבת מברכים תהלים':
								$html .= "שבת מברכים תהלים - Saying my quota of Kapitaclachon Shabbos Mevorchim";
								break;
							case 'תפלה':
								$html .= "תפלה - Strengthening our relationship with Hashem";
								break;
							case 'אבות ובנים':
								$html .= "אבות ובנים - Building a connection with my parents by learning and helping them";
								break;
							case 'התקשרות':
								$html .= "התקשרות- Strengthen our connection with the Rebbe";
								break;
							case 'יומי דפגרא':
								$html .= "יומי דפגרא- Celebrating YomimTovim as a Chayol";
								break;
							case 'מבצעים':
								$html .= "מבצעים - Helping other Yidden";
								break;
							case 'חשבון הנפש':
								$html .= "חשבון הנפש - Waking up and going to Sleep as a Chayol";
								break;
							case 'והלכת בדרכיו':
								$html .= "והלכת בדרכיו - Learning from the Avos and Imohos how to fight myYetzerHora";
								break;
							case 'ניגונים':
								$html .= "ניגונים - Learning and singing Nigunim";
								break;
							case 'תניא בעל פה':
								$html .= "תניא בעל פה – Learning Tanya by heart";
								break;
							case 'חתת':
								$html .= "חתת - Saying the daily portion of Tehillim";
								break;
							case 'ספר המצוות':
								$html .= "ספר המצוות - Learning the 613 Mitzvos of the Torah";
								break;
							case 'חשבן הנפש':
								$html .= "חשבון הנפש- Waking up and going to Sleep as a Chayol";
								break;
						}
						$html .= "</td></tr>";
						$oldSubject = $subject;
					}
					foreach ($arr as $task => $info) {
						$html .= "<tr><td class='sticker'><img src='http://mashpia.com/images/stickers/Sticker-" . $stickers[$subject] .  ".gif'></td>"; 
						$html .= "<td>" . $task . "</td>";
						if (isset($info['potential'])) {
							$data = array(
								'id'	=>	$user, 
								'start'	=>	$this->start, 
								'end'	=>	$this->end, 
								'subject' => $subjects[$subject], 
								'task'	=>	$task
							);
							$data = http_build_query($data);
							$html .= "<td class='total'>";
							if ($email) 
								$html .= "<a href=''>";
							else 
								$html .= "<a href='detailedReport.php?$data'>";
							$potential = $info['potential'];
							$actual = $info['actual'];
							if (($info['potential'] == 1 && $info['actual'] == 0) || 
								($info['potential'] == 7 && $info['actual'] < 5)) {
								$html .= "<span style='color: red'>" . $info['actual'] . "</span>";
							} else {
								$html .= $info['actual'];
							}
							$html .= " / " . $info['potential'] . "</a></td></tr>";				
						} 
					}
				}			
			$html .= "
				</table>
				<br />
				<div style='page-break-after: always'></div>
				</div>";
			
			if (!$email) {
				echo $html;
			} else {
				//$to = $userInfo[$user]['email'];
				$to = 'naftolir@gmail.com';
				
				if (!empty($to)) {
					//prepare email
					$subject = $this->emailSubject;
		
					// To send HTML mail, the Content-type header must be set
					$headers  = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
					$headers .= "From: " . $this->from . "\r\n";
					$headers .= "Reply-To: " . $this->reply . "\r\n";
						
					$body = "Dear Parent(s) of " . $userInfo[$user]['first'] . ' ' . $userInfo[$user]['last'] . ",<br /><br />";
					$body .= $html . "<br /><br />";
					$body .= "Sincerely,<br />";
					if ($this->signed == 1) {
						$body .= $this->signature;
					} else if ($this->signed == 2) {
						$school_class = (array)$userInfo[$user]['school_class'];
						$body .= $school_class['class_teacher'];
					}
					
					if (mail($to, $subject, $body, $headers)) {
						$emailed[] = $to;
					}
				}
			}
		}

		if ($email) {
			return $emailed;
		}
	}

	public function setEmailProps($props) {
		$this->signed = $props['signed'];
		$this->signature = $props['signature']; 
		$this->emailSubject = $props['emailSubject'];
		$this->from = $props['from'];
		$this->reply = $props['reply'];
	}
}
?>