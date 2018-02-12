<?
class ShabbosMevorchim {
    
    private $dates;
    private $rDates;
    private $showDone;
    
    private $school_id;
    private $school_name;
    private $classes;
    
    private $tasks;
    private $armyResults;
    private $armyDoneResults;
    private $schoolResults;
    private $schoolDoneResults;
    private $classResults; 
    private $classDoneResults;
    private $accomplished;
    
    private $armySchoolsResults;
    private $armySchoolsDoneResults;
    private $armyResultsOrdered;
    private $accomplishedRows;
    private $accomplishedKapitelach;
    private $accomplishedMinutes;
    
    private $pieKapitelach;
    private $pieMinutes;
    
    private $db;   
        
    public function __construct() {
        //set db handle
        require_once 'class.db.php';
        $this->db = DB::getInstance();    
        $sm5773 = calculateSM(5773);
        $sm5774 = calculateSM(5774);
		echo "<pre>";
		//print_r($sm5773);
		//print_r($sm5774);
		$months = array(
            1   =>  'Cheshvon', 
            2   =>  'Kisleiv', 
            3   =>  'Teves', 
            4   =>  'Shevat', 
            5   =>  'Adar', 
            6   =>  'Adar II', 
            7   =>  'Nissan',  
            8   =>  'Iyar', 
            9   =>  'Sivan', 
            10  =>  'Tamuz', 
            11  =>  'Av', 
            12  =>  'Elul', 
            13  =>  'Tishrei'
        );
        $this->dates = array(
        	$months[12] => $sm5773[12], 
        	$months[13]	=> $sm5773[13]
		);
        foreach ($sm5774 as $k => $v) {
            if ($k == 12) break;
            if ($sm5774[6] == $sm5774[7]) {
                if ($k == 5) {
                    $this->dates['Adar I'] = $v;
                    continue;
                } else if ($k == 6) {
                    continue;
                } 
            }
            $this->dates[$months[$k]] = $v;
        }
		//print_r($this->dates);
		echo "</pre>";
        $this->tasks = array(
            'Kapitelach'  =>  'How many Kapitlach did you say?',
            'Minutes'   =>  'How many minutes did you spend saying תהלים?'
        );
        $this->accomplished = array();
        $this->rDates = array();
        $this->showDone = array();
    }
    
    public function getTasks() {
        return $this->tasks;
    }
    
    public function getKeys() {
        return array_keys( $this->tasks );
    }
    
    public function setReportDates() {
        $today = unixtojd();
        foreach ( $this->dates as $month => $date ) {
            //echo $today . " : " . $month . "-" . $date . "<br />";
            $this->rDates[$month] = $date;
            if ( $today < ($date + 28) ) {
                //if it's just before shabbos mevorchim don't show done data
                if ( $today >= $date ) {
                    $this->showDone[] = $date;
                }
                break; //end looping by current shabbos mevorchim date
            } else {
                $this->showDone[] = $date;
            }
        }
    }
    
    public function getReportDates() {
        return $this->rDates;
    }
    
    public function setArmyResults() {
        
        $sql1 = "SELECT sum( dt.quantity ) AS total
                FROM date_tasks dt
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN user_tracks ut
                USING ( track_id,
                LEVEL , subject_id )
                JOIN users u
                USING ( user_id ) 
                JOIN schools s 
                USING (school_id) 
                JOIN classes c 
                USING (class_id) 
                WHERE ut.subject_id =1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND dt.name = ?  
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered > 0
                AND ut.enrolled =1 
                AND s.school_era is null 
                AND c.class_era = 0";
                        
        $stmt1 = $this->db->prepare( $sql1 ); 
                
        $sql2 = "SELECT sum( dt.done_qty ) AS total
                FROM date_tasks_marks dt
                JOIN date_tasks
                USING ( date_task_id )
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN users u 
                USING ( user_id ) 
                JOIN user_tracks ut
                USING ( user_id, subject_id, level, track_id ) 
                JOIN schools s 
                USING (school_id) 
                JOIN classes c 
                USING (class_id) 
                WHERE dtm.subject_id =1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND date_tasks.name = ? 
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0 
                AND ut.enrolled =1 
                AND s.school_era is null 
                AND c.class_era = 0";
        
        $stmt2 = $this->db->prepare( $sql2 );                

        foreach ( $this->rDates as $month => $date ) {
            
            foreach ( $this->tasks as $key => $task ) {
                           
                $stmt1->execute( array( $date, $date, $task ) );
                $row1 = $stmt1->fetch( PDO::FETCH_ASSOC );
                $this->armyResults[$key][$date] = $row1['total'];
             
                $stmt2->execute( array( $date, $date, $task ) );
                $row2 = $stmt2->fetch( PDO::FETCH_ASSOC );
                $this->armyDoneResults[$key][$date] = $row2['total'];
                
            }            
        }
    }
    
    public function getArmyResults() {
        return $this->armyResults;
    }
    
    public function getArmyDoneResults() {
        return $this->armyDoneResults;
    }
    
    public function setSchool( $id ) {
        $this->school_id = $id;
        $sql = "select school_name from schools where school_id = " . $this->school_id;
        foreach ( $this->db->query( $sql ) as $row )
            $this->school_name = $row['school_name'];
    }
    
    public function getSchoolID() {
        return $this->school_id;
    }
    
    public function getSchoolName() {
        return $this->school_name;
    }
    
    public function setSchoolResults( $id ) {
        
        $sql1 = "SELECT sum( dt.quantity ) AS total
                FROM date_tasks dt
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN user_tracks ut
                USING ( track_id,
                LEVEL , subject_id) 
                JOIN users u
                USING ( user_id )
                JOIN classes c ON ( c.class_id = u.class_id )
                WHERE u.school_id = ?  
                AND ut.subject_id =1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND dt.name = ?  
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0 
                and c.class_era = 0 
                AND ut.enrolled =1";
                
        $stmt1 = $this->db->prepare( $sql1 );
        
        $sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM users u
                JOIN (
                date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
                ) ON ( dtm.user_id = u.user_id
                AND dt.date_task_id = dtm.date_task_id
                AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
                AND dtmm.start_date = ? 
                AND dtmm.end_date = ? 
                AND dt.name = ?  )
                WHERE u.school_id = ?  
                AND u.user_registered >0";
        
        $stmt2 = $this->db->prepare( $sql2 );
                
        foreach ( $this->rDates as $month => $date ) {
            
            foreach ( $this->tasks as $key => $task ) {
            	//echo time();
                $t1 = time();            
                $stmt1->execute( array( $id, $date, $date, $task ) );
                $t2 = time();
                echo "First query:<br />Before: $t1 After: $t2<br />";
                $row1 = $stmt1->fetch( PDO::FETCH_ASSOC );
                $this->schoolResults[$key][$date] = $row1['total'];
                
                $t1 = time();
                $stmt2->execute( array( $date, $date, $task, $id ) );
                $t2 = time();
                echo "Second query:<br />Before: $t1 After: $t2<br />";
                $row2 = $stmt2->fetch( PDO::FETCH_ASSOC );
                $this->schoolDoneResults[$key][$date] = $row2['total'];
            }            
        }
    }

    public function setASR( $date ) {
        $sql = "select school_id from schools 
                join school_subjects s using (school_id) 
                where school_era is null 
                and s.subject_id = 1 
                and school_id not in (82) 
                order by school_name";
        foreach ( $this->db->query( $sql ) as $row ) {
            $this->setArmyDoneSchoolsResults( $row['school_id'], $date );
            $this->setArmySchoolsResults( $row['school_id'], $date );
            $this->setArmyResultsOrdered();
            $this->setAccomplishedRows();
        }
    }
      
    private function setArmySchoolsResults( $id, $date ) {
                
        $sql1 = "SELECT sum( dt.quantity ) AS total
                FROM date_tasks dt
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN user_tracks ut
                USING ( track_id,
                LEVEL , subject_id) 
                JOIN users u
                USING ( user_id )
                JOIN classes c ON ( c.class_id = u.class_id )
                WHERE u.school_id = ?     
                AND ut.subject_id =1
                AND dtm.start_date = ?  
                AND dtm.end_date = ?  
                AND dt.name = ?   
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0
                AND ut.enrolled =1"; 
                    
        $stmt1 = $this->db->prepare( $sql1 );
        foreach ( $this->tasks as $key => $task ) {
            $stmt1->execute( array( $id, $date, $date, $task ) );
            $row1 = $stmt1->fetch( PDO::FETCH_ASSOC );
            $this->armySchoolsResults[$key][$id] = $row1['total'];
        }         
    }
    
    private function setArmyDoneSchoolsResults( $id, $date ) {
        
        $sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM date_tasks_marks dtm 
                JOIN users u 
                USING (user_id) 
                JOIN date_tasks dt 
                USING (date_task_id) 
                JOIN date_tasks_missions dtmm 
                USING ( date_tasks_mission_id ) 
                WHERE u.school_id = ?     
                AND dtmm.subject_id =1
                AND dtmm.start_date = ?  
                AND dtmm.end_date = ?  
                AND dt.name = ?   
                AND dtmm.school_type_id = u.school_type_id";
                        
        $stmt2 = $this->db->prepare( $sql2 );         
        foreach ( $this->tasks as $key => $task ) {
            //echo "ID: " . $id . " Date: " . $date . " Task: " . $task . "<br />" . $sql2 . "<br />";               
            $stmt2->execute( array( $id, $date, $date, $task ) );
            $row2 = $stmt2->fetch( PDO::FETCH_ASSOC );
            $this->armySchoolsDoneResults[$key][$id] = $row2['total'];
        }
    }
    
    private function setArmyResultsOrdered() {
        foreach ( $this->armySchoolsResults as $key => $info ) {
            foreach ( $info as $id => $total ) {
                if ( $this->armySchoolsResults[$key][$id] == 0 ) continue; 
                $this->armyResultsOrdered[$key][$id] = 
                    round( ($this->armySchoolsDoneResults[$key][$id] / $this->armySchoolsResults[$key][$id]) * 100, 1 );
            }
            //sort array
            arsort( $this->armyResultsOrdered[$key] );
        }
    }
    
    private function setAccomplishedRows() {
        foreach ( $this->armyResultsOrdered as $key => $info ) {
            foreach ( $info as $id => $value ) {
                if ( $id == 61 ) continue; 
                $this->setSchool( $id );
                $name = $this->getSchoolName(); 
                $this->accomplishedRows[$key][$name][$this->armySchoolsResults[$key][$id]] = 
                    is_null( $this->armySchoolsDoneResults[$key][$id] ) ? 0 : 
                        (int)$this->armySchoolsDoneResults[$key][$id];
            }
            if ( $key == 'Kapitelach' ) {
                $this->accomplishedKapitelach = $this->accomplishedRows[$key];
                $this->pieKapitelach = $this->armySchoolsResults[$key];
            } else if ( $key == 'Minutes' ) {
                $this->accomplishedMinutes = $this->accomplishedRows[$key];
                $this->pieMinutes = $this->armySchoolsResults[$key];
            }            
        }
    }
    
    public function getAccomplishedKapitelach() {
        return $this->accomplishedKapitelach;
    }
    
    public function getAccomplishedMinutes() {
        return $this->accomplishedMinutes;
    }
    
    public function getPieKapitelach() {
        $temp = array();
        foreach ( $this->pieKapitelach as $id => $value ) {
            $this->setSchool($id);
            $name = $this->getSchoolName();
            $temp[$name] = $value;
        } 
        return $temp;
    }
    
    public function getPieMinutes() {
        return $this->pieMinutes;
    }
    
    public function getArmyResultsOrdered() {
        return $this->armyResultsOrdered;
    }
    
    public function getSchoolResults() {
        return $this->schoolResults;
    }
    
    public function getSchoolDoneResults() {
        return $this->schoolDoneResults;
    }
    
    private function setClasses() {
        $sql = "SELECT DISTINCT c . *
                FROM classes c
                JOIN schools s
                USING ( school_id )
                JOIN users u
                USING ( class_id )
                WHERE s.school_id = $this->school_id 
                AND u.user_registered >0
                AND c.class_era =0
                ORDER BY c.class_grade, c.class_sub";
        foreach ( $this->db->query( $sql ) as $row ) {
            $this->classes[$row['class_id']]['grade'] = 
                $row['class_sub'] == "" ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
            $this->classes[$row['class_id']]['teacher'] = $row['class_teacher'];
        }
    }
    
    public function setClassResults() {
        
        $this->setClasses(); 
        
        $sql1 = "SELECT sum( dt.quantity ) AS total
                FROM date_tasks dt
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN user_tracks ut
                USING ( track_id,
                LEVEL , subject_id )
                JOIN users u
                USING ( user_id )
                JOIN classes c ON ( c.class_id = u.class_id )
                WHERE u.class_id = ?  
                AND ut.subject_id =1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND dt.name = ? 
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0
                AND ut.enrolled =1";
                    
        $stmt1 = $this->db->prepare( $sql1 );
        
        $sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM classes c, users u
                JOIN (
                date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
                ) ON ( dtm.user_id = u.user_id
                AND dt.date_task_id = dtm.date_task_id
                AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
                AND dtmm.start_date = ? 
                AND dtmm.end_date = ? 
                AND dt.name = ? )
                WHERE u.class_id = c.class_id
                AND c.class_id = ?  
                AND u.user_registered >0";
                    
        $stmt2 = $this->db->prepare( $sql2 );

        foreach ( $this->rDates as $month => $date ) {
            
            foreach ( $this->tasks as $key => $task ) {
                    
                foreach ( $this->classes as $class => $info ) {    
                               
                    $stmt1->execute( array( $class, $date, $date, $task ) );
                    $row1 = $stmt1->fetch( PDO::FETCH_ASSOC );
                    $this->classResults[$key][$date][$class] = $row1['total'];
                                       
                    $stmt2->execute( array( $date, $date, $task, $class ) );
                    $row2 = $stmt2->fetch( PDO::FETCH_ASSOC );
                    $this->classDoneResults[$key][$date][$class] = $row2['total'];
                    
                    //find out which classes accomplished their goals
                    if ( $this->classDoneResults[$key][$date][$class] >= $this->classResults[$key][$date][$class] ) {
                        $this->accomplished[$key][$date][$info['grade']] = $info['teacher'];
                    }
                }
            }            
        }
    }
    
    public function getClassResults() {
        return $this->classResults;
    }
    
    public function getClassDoneResults() {
        return $this->classDoneResults;
    }
    
    public function getAccomplished() {
        return $this->accomplished;
    }
    
    public function showDone( $date ) {
        if ( in_array( $date, $this->showDone ) ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function generateArmyTable( $month, $date ) {
        ?>
        
        <table>
            <tr>
                <th>Army Wide Goal</th>
                <? if ( $this->showDone( $date ) ) { ?>
                <th>Army Wide Accomplishment</th>
                <? } ?>
            </tr>
 
        <? foreach ( $this->tasks as $key => $task ) { ?>            
            <tr>
                <td><?=number_format( $this->armyResults[$key][$date] )?> <?=$key?></td>
                <? if ( $this->showDone( $date ) ) { ?>
                <td><?=number_format( $this->armyDoneResults[$key][$date] )?> <?=$key?></td>
                <? } ?>
            </tr>
        <? } ?>
        </table>
        
        <?
    }
    
    public function generateBaseTable( $month, $date ) {
        ?>
        
        <table>
            <tr>
                <th>Base Goal</th>
                <? if ( $this->showDone( $date ) ) { ?>
                <th>Base Accomplishment</th>
                <? } ?>
            </tr>
            
       <? foreach ( $this->tasks as $key => $task ) { ?> 
            <tr>
                <td><?=number_format( $this->schoolResults[$key][$date])?> <?=$key?></td>
                <? if ( $this->showDone( $date ) ) { ?>
                <td><?=number_format( $this->schoolDoneResults[$key][$date])?> <?=$key?></td>
                <? } ?>
            </tr>
        <? } ?>
        </table>
        
        <?
    }
    
    public function generateSummary( $month, $date ) {
        ?>
        
        <table>
            <tr>
                <th>Platoon</th>
                <th>Teacher</th>
                <th>Platoon Goal</th>
                <? if ($this->showDone( $date )) { ?>
                <th>Platoon Accomplishment</th>
                <th>More than Goal</th>
                <th>Less than Goal</th>
                <? } ?>
            </tr>
            
        <? foreach ( $this->classes as $class_id => $class ) { ?>   
            
            <? foreach ( $this->tasks as $key => $task ) { ?>
            
                <tr>
                    <td><?=$class['grade']?></td>
                    <td><?=$class['teacher']?></td>        
                    <td><?=number_format( $this->classResults[$key][$date][$class_id] )?> <?=$key?></td>
                    <? if ($this->showDone( $date )) { ?>
                        <td><?=number_format( $this->classDoneResults[$key][$date][$class_id] )?> <?=$key?></td>
                        <? if ( $this->classDoneResults[$key][$date][$class_id] > $this->classResults[$key][$date][$class_id] ) { ?>
                        <td><?=( $this->classDoneResults[$key][$date][$class_id]-$this->classResults[$key][$date][$class_id] )?> <?=$key?></td>
                        <td>&nbsp;</td>    
                        <? } else if ( $this->classDoneResults[$key][$date][$class_id] < $this->classResults[$key][$date][$class_id] ) { ?>
                        <td>&nbsp;</td>
                        <td><?=( $this->classResults[$key][$date][$class_id]-$this->classDoneResults[$key][$date][$class_id] )?> <?=$key?></td>
                        <? } else { ?>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>    
                        <? } ?>
                    <? } ?>
                </tr>
                
            <? } ?>
            
        <? } ?>
        
        </table>
        <?
    }

    public function generateReport() {
        
        foreach ( $this->classes as $class_id => $class ) {
        ?>
               
            Base name: <?=$this->school_name?><br />
            Platoon: <?=$class['grade']?><br />
            Teacher: <?=$class['teacher']?><br />
            <br />
            
            <? foreach ( $this->rDates as $month => $date ) { ?> 
                                    
                <p>Shabbos Mevorchim <?=$month?></p>
                <table>
                    
                <? if ( $this->showDone ( $date ) ) { ?>
                    <tr>
                        <th>Our Platoon Goal</th>
                        <th>Our Platoon Accomplishment</th>
                        <th>Our Base Goal</th>
                        <th>Our Base Accomplishment</th>
                        <th>Army Wide Goal</th>
                        <th>Army Wide Accomplishment</th>
                    </tr>
                    
                    <? foreach ( $this->tasks as $key => $task ) { ?>
                            <tr>
                                <td><?=number_format( $this->classResults[$key][$date][$class_id] )?> <?=$key?></td>
                                <td><?=number_format( $this->classDoneResults[$key][$date][$class_id] )?> <?=$key?></td>
                                <td><?=number_format( $this->schoolResults[$key][$date] )?> <?=$key?></td>
                                <td><?=number_format( $this->schoolDoneResults[$key][$date] )?> <?=$key?></td>
                                <td><?=number_format( $this->armyResults[$key][$date] )?> <?=$key?></td>
                                <td><?=number_format( $this->armyDoneResults[$key][$date] )?> <?=$key?></td>
                            </tr>
                     <? }
                    } else { 
                 ?> 
                    <tr>
                        <th>Our Platoon Goal</th>
                        <th>Our Base Goal</th>
                        <th>Army Wide Goal</th>
                    </tr>
                    
                    <? foreach ( $this->tasks as $key => $task ) { ?>
                        <tr>
                            <td><?=number_format( $this->classResults[$key][$date][$class_id] )?> <?=$key?></td>
                            <td><?=number_format( $this->schoolResults[$key][$date] )?> <?=$key?></td>
                            <td><?=number_format( $this->armyResults[$key][$date] )?> <?=$key?></td>
                        </tr>
                    <? 
                        }
                    } 
                ?>
    
                </table>
                <br />
           <? } ?>
           
            <p align="right"> היום יום - כ"ה שבט </p>
            <p align="right"> 
            גּוֹמֵר זַיין דעֶם תְּהִלִּים שַׁבָּת מְבָרְכִים - דאָס דאַרף מעֶן אָפּהִיטעֶן, דאָס אִיז נוֹגֵעַ אִיהם, זַיינעֶ קִינְדעֶר אוּן קִינְדס קִינְדעֶר
            </p>    
            <p>
                "One should be careful to say Tehillim everyday and to say the whole Tehillim on Shabbos Mevorchim.
                These things are important for every person, his children and grandchildren."
            </p>
            <div class='page-break'></div>
            <hr />
        
        <?
        }
    }

    public function generateAccomplishedReport() {
        
        if ( !empty( $this->accomplished ) ) {
        
            $keys = array_keys( $this->tasks ); 
            ?>
            
            <h2>Platoons that accomplished their goals</h2>
            
            <? foreach ( $keys as $key ) {  
                
                if ( !empty( $this->accomplished[$key] ) ) {
                                    
                    if ( $key == 'Kapitelach' ) 
                        echo "<div style='float: left'>";
                    else 
                        echo "<div style='float: right'>";
                    
                    echo "<p><b>" . $key . "</b></p>";
                    
                    foreach ( $this->rDates as $month => $date ) { 
                        if ( array_key_exists( $date, $this->accomplished[$key] ) ) { ?> 
                            <p>Shabbos Mevorchim <?=$month?></p>
                            <table>
                                <tr>
                                    <th>Grade</th>
                                    <th>Teacher</th>
                                </tr>
                                <? foreach ( $this->accomplished[$key][$date] as $grade => $teacher ) { ?>
                                    <tr>
                                        <td><?=$grade?></td>
                                        <td><?=$teacher?></td>
                                    </tr>
                                <? } ?>
                            </table>
                            <br />
                        <? }
                    } 
                    
                    echo "</div>";
                    
                }
            }
        }
    }

    public function generateArmyAccomplishedReport() {
        
        foreach ( $this->armyResultsOrdered as $key => $results ) {
            echo "<h3>" . $key . "</h3>";  
        ?> 
            <br />
            <table>
                <tr>
                    <th>School Name</th>
                    <th>Goal</th>
                    <th>Accomplished</th>
                    <th>More than Goal</th>
                    <th>Less than Goal</th>
                </tr>
                <?
                    foreach ( $results as $id => $total ) {
                        $this->setSchool( $id );
                        $goal = $this->armySchoolsResults[$key][$id]; 
                        $done = $this->armySchoolsDoneResults[$key][$id];
                        echo "<tr><td>" . $this->school_name . "</td><td>" . $goal . 
                                "</td><td>" . $done . " <span class='percent'>(" . 
                                $this->armyResultsOrdered[$key][$id] . "%)</span></td>";                            
                        if ( $done > $goal ) {
                            echo "<td>" . ($done - $goal) . "</td><td>&nbsp;</td></tr>";
                         } else if ( $goal > $done ) {
                            echo "<td>&nbsp;</td><td>" . ($goal - $done) . "</td></tr>";
                        } else {
                            echo "<td>&nbsp;</td><td>&nbsp;</td></tr>";
                        }
                    }
                ?>
            </table>
            <br />
            <div class='page-break'></div>
            <?
        }
    }

	public function createSMTableInfo() {

		foreach ($this->armyResults as $task => $info) {
			foreach ($info as $date => $amount) {
				$sql = "insert into sm values('', '" . array_search($date, $this->rDates) . "', $date, '$task', 'army', 0, $amount, 0)";
				echo $sql . "<br />";				
			}
		}
		foreach ($this->armyDoneResults as $task => $info) {
			foreach ($info as $date => $amount) {
				$sql = "update sm set accomplished = $amount where date = $date and task = '$task' and type = 'army'";
				echo $sql . "<br />";
			}
		}
	}
}
?>