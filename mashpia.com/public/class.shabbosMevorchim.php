<?php

class ShabbosMevorchim
{
    private $dates;
    private $rDates;
    private $showDone;
    private $rDatesPlusPrevious;
    private $rDatesAll;

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

    private $users;
    private $usersResults;
    private $usersDoneResults;

    private $students;
    private $studentResults;
    private $studentDoneResults;

    private $months;
    private $heMonths;

    private $totalUsers;
    private $schools;
    private $doneQuotas;
    private $participated;
    private $numRegistered;

    private $accomplishedOnly;
    private $debug;
    private $backup;
    private $backup_ran_by_date = [];

    public function __construct($year = 0)
    {
        if ($year == 0) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php");
            $year = GlobalSettings::getCurrentYear();
        }

        require_once($_SERVER['DOCUMENT_ROOT'] . "/tehillim_backups/class.tehillimBackup.php"); // load the backups class
        $this->backup = new TehillimBackup();

        // load the DBS connection....
        require_once(dirname(__FILE__) . '/class.db.php');
        $this->db = DB::getInstance();
        // cacluate the shabbos mevorchim dates (julian dates) for a given year.
        $sm = calculateSM($year); // calculateSM is defined in ~/public_html/db.php#887

        // transalate the numbers into names of the months....
        $this->months = array(
            0 => 'Tishrei', 1 => 'Cheshvon', 2 => 'Kisleiv', 3 => 'Teves',
            4 => 'Shevat', 5 => 'Adar', 6 => 'Adar II', 7 => 'Nissan',
            8 => 'Iyar', 9 => 'Sivan', 10 => 'Tamuz', 11 => 'Av',
            12 => 'Elul'
        );
        $this->heMonths = array(
            0 => 'תשרי', 1 => 'חשון', 2 => 'כסלו', 3 => 'טבת',
            4 => 'שבט', 5 => 'אדר', 6 => 'אדר ב', 7 => 'ניסן',
            8 => 'אייר', 9 => 'סיון', 10 => 'תמוז', 11 => 'אב',
            12 => 'אלול'
        );

        // go through all of the sm dates...
        foreach ($sm as $index => $sm_date) {
            // if this is not a leap year (months 6 and 7 are different...)
            if ($sm[6] != $sm[7] && $index == 5) { // and we are on Adar...
                $this->dates['Adar I'] = $sm_date; // set Adar to Adar I
                continue; // go to the next month...
            } else if ($sm[6] == $sm[7] && $index == 6) { // if the two are the same and we are up to number 6...
                continue; // just skip it..
            }
            $this->dates[$this->months[$index]] = $sm_date;
        }

        // tasks array was modified to point to grid id instead of short name to account for multiple langauges
        $this->tasks = array(
            'Kapitelach' => 8001,
            'Minutes' => 8002
        );

        // set the following variables to empty arrays...
        $this->accomplished = array();
        $this->rDates = array();
        $this->showDone = array();
        $this->users = array();
        $this->usersResults = array();
        $this->usersDoneResults = array();
        $this->students = array();
        $this->studentResults = array();
        $this->studentDoneResults = array();
        // set the following variables to false by default..
        $this->accomplishedOnly = false;
        $this->debug = false;
    }

    public function getBackupRan($date)
    {
        //        $backupDateIndex = array_search( $date, array_values( $this->dates ) );
        // if there is an index, check if it is set. Otherwise just return false
//        if ( $backupDateIndex === false ) return false;
        // if we have a date at this index, make sure it is before today
//        if ( isset( $this->backup->dates[$backupDateIndex] ) )
//            return $this->backup->dates[$backupDateIndex] <= unixtojd();

        // cache results so they aren't reran for every user
        if (array_key_exists($date, $this->backup_ran_by_date)) {
            return $this->backup_ran_by_date[$date];
        }
        // check if backup actually ran
        $stmt = $this->db->prepare("
            SELECT * FROM tehillim_backups WHERE sm_date = :date
        ");
        $stmt->execute([
            ':date' => $date
        ]);
        $rows = $stmt->fetch();
        $result = !empty($rows);
        $this->backup_ran_by_date[$date] = $result;
        return $result;
        // by default we did not run the backup
//        return false;
    }

    public function setAccomplishedOnly()
    {
        $this->accomplishedOnly = true;
    }

    public function setDebug()
    {
        $this->debug = true;
    }

    /**
     * getHebrewMonth
     *
     * @param string $key English name of month.
     * @return string
     */
    public function getHebrewMonth($key)
    {
        $index = array_search($key, $this->months);
        return $this->heMonths[$index];
    }

    /**
     * getHebrewMonth
     *
     * @param string $key English name of month.
     * @return string
     */
    public function getHebrewMonthFromJd($jd)
    {
        $month = array_search($jd, $this->dates);
        $index = array_search($month, $this->months);
        return $this->heMonths[$index];
    }


    public function getTasks()
    {
        return $this->tasks;
    }

    public function getKeys()
    {
        return array_keys($this->tasks);
    }

    /**
     * setReportDates
     *
     * @param integer $date Julian date to create the reports for.
     * @return void
     */
    public function setReportDates($date = 0)
    {
        $today = unixtojd(); // get the curent unix date..
        // if a date was passed in...
        if ($date > 0) {
            $month = array_search($date, $this->dates); // get the name of the month.
            // TODO figure out where these are being used.
            $this->rDates[$month] = $date; // set the date for this month to the internal rDates array.
            if ($date < $today) $this->showDone[] = $date; // add the date to the showDone array if it's less than today
            // if no date was passed in.
        } else {
            $dates = array();
            foreach ($this->dates as $month => $date) {
                //echo $today . " : " . $month . "-" . $date . "<br />";
                $dates[$month] = $date; // add the date to the function's date array if we have a backup
                if ($today < ($date + 28)) { // if the date is less then one month before today:
                    // if it's just before shabbos mevorchim don't show done data
                    if ($today >= $date) {
                        $this->showDone[] = $date;
                    }
                    break; //end looping by current shabbos mevorchim date
                } else {
                    $this->showDone[] = $date; // add the date to the dates array.
                }
            }
            // get the last date
            $d = end($dates);
            $month = key($dates); // and the month
            // update the rdates array
            $this->rDates[$month] = $d;
            // go back one in the dates array.
            prev($dates);
            // set rDatesPlusPrevious to match the previous months version of rDates
            $this->rDatesPlusPrevious[key($dates)] = current($dates);
            $this->rDatesPlusPrevious[$month] = $d;
            // add all of them to the array rDatesAll
            foreach ($dates as $month => $date) {
                $this->rDatesAll[$month] = $date;
            }
        } // end if no date was provided.
    } // end function setReportDates( $date )

    public function setQuotaCardDates()
    {
        $today = unixtojd(); // get the curent unix date..
        $dates = array();
        foreach ($this->dates as $month => $date) {
            //echo $today . " : " . $month . "-" . $date . "<br />";
            $dates[$month] = $date; // add the date to the function's date array if we have a backup
            if ($date >= $today) break;
        }
        // get the last date
        $d = end($dates);
        $month = key($dates); // and the month
        // update the rdates array
        $this->rDates[$month] = $d;
    }

    public function getReportDates()
    {
        return $this->rDates;
    }

    public function getReportDatesPlusPrevious()
    {
        return $this->rDatesPlusPrevious;
    }

    public function getReportDatesAll()
    {
        return $this->rDatesAll;
    }

    public function setArmyResults()
    {
        // select the total tehillim marks for each kid in each campaign
        $sql1 = "SELECT sum( dt.quantity ) AS total
            FROM date_tasks dt
            JOIN date_tasks_missions dtm USING ( date_tasks_mission_id )
            JOIN user_tracks ut USING ( track_id, LEVEL, subject_id )
            JOIN users u USING ( user_id ) 
            JOIN schools s USING (school_id) 
            JOIN classes c USING (class_id) 
            WHERE ut.subject_id = 1
            AND ut.enrolled = 1
            AND dtm.start_date = ?
            AND dtm.end_date = ?
            AND dtm.school_type_id = u.school_type_id
            AND dt.grid_id = ?
            AND s.school_era is null
            AND c.class_era = 0
            AND u.user_registered > 0
        AND u.user_id = ut.user_id
        AND u.lang_id = dtm.lang_id";
        $stmt1 = $this->db->prepare($sql1);

        // select the total done from date_tasks_missions for this mission in general
        $sql2 = "SELECT sum( dtm.done_qty ) AS total 
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                JOIN date_tasks_missions dtmm USING (date_tasks_mission_id) 
                WHERE dtmm.start_date = ?
                AND dtmm.end_date = ?
                AND dt.grid_id = ?";
        $stmt2 = $this->db->prepare($sql2);

        //$sqlQuota = "select sum( tb.quota ) as total
        //			from tehillim_backups tb
        //			where tb.sm_date = ?
        //			and tb.grid_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        // select the totals from the backup table ( pre-deadline )
        $sqlBackup = "SELECT IFNULL(sum( tb.done_qty ), 0) AS total
                    FROM tehillim_backups tb 
                    WHERE tb.sm_date = ? 
                    AND tb.grid_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        // for each date that we are generating this report for
        foreach ($this->rDates as $date) {
            // for each task (defined on line 89)
            foreach ($this->tasks as $key => $task) {
                // skip the minutes task
                if ($key == 'Minutes') continue;

                if ($this->debug) echo "Before army wide quotas: " . time() . "<br />";
                $stmt1->execute(array($date, $date, $task));
                if ($this->debug) echo "After army wide quotas: " . time() . "<br />";
                $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                $this->armyResults[$key][$date] = $row1['total'];

                // figure out if we are getting results from backup table or not
                $stmtBackup->execute(array($date, $task));
                $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
                if ($this->debug) {
                    echo "<pre>";
                    print_r($rowBackup);
                    echo "</pre>";
                }

                // if there is info in the backup table use that.
                if ($rowBackup['total'] > 0) {
                    $this->armyDoneResults[$key][$date] = $rowBackup['total'];
                } else { // otherwise, generate the report and pull up the results.
                    $stmt2->execute(array($date, $date, $task));
                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $this->armyDoneResults[$key][$date] = $row2['total'];
                }
            }
        }
    }

    public function getArmyResults()
    {
        return $this->armyResults;
    }

    public function getArmyDoneResults()
    {
        return $this->armyDoneResults;
    }

    public function setSchool($id)
    {
        $this->school_id = $id;
        $sql = "SELECT school_name FROM schools WHERE school_id = " . $this->school_id;
        foreach ($this->db->query($sql) as $row)
            $this->school_name = $row['school_name'];
    }

    public function getSchoolID()
    {
        return $this->school_id;
    }

    public function getSchoolName()
    {
        return $this->school_name;
    }

    public function setSchoolResults($id, $minutes = false)
    {
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
                AND dt.grid_id = ?  
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0 
                and c.class_era = 0 
                AND ut.enrolled = 1
				AND u.user_id = ut.user_id
				AND u.lang_id = dtm.lang_id";

        $stmt1 = $this->db->prepare($sql1);
        /*
        $sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM users u
                JOIN (
                date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
                ) ON ( dtm.user_id = u.user_id
                AND dt.date_task_id = dtm.date_task_id
                AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
                AND dtmm.start_date = ? 
                AND dtmm.end_date = ? 
                AND dt.grid_id = ?  )
				JOIN user_tracks ut using (subject_id, track_id, level) 
                WHERE u.school_id = ?  
                AND u.user_registered > 0
				AND u.user_id = ut.user_id
				AND u.lang_id = dtmm.lang_id";
		*/
        /*
		$sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM users u
                JOIN (
                date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
                ) ON ( dtm.user_id = u.user_id
                AND dt.date_task_id = dtm.date_task_id
                AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
                AND dtmm.start_date = ? 
                AND dtmm.end_date = ? 
                AND dt.grid_id = ?  )
				JOIN user_tracks ut using (subject_id, track_id, level) 
                WHERE u.school_id = ?  
                AND u.user_registered > 0
				AND u.user_id = ut.user_id";
        */
        $sql2 = "SELECT sum( dtm.done_qty ) AS total 
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                join users u using (user_id) 
                where dtmm.start_date = ?
                and dtmm.end_date = ?
                and dt.grid_id = ?
                and u.school_id = ?";

        $stmt2 = $this->db->prepare($sql2);

        //$sqlQuota = "select sum( tb.quota ) as total
        //			from tehillim_backups tb
        //			JOIN users u using ( user_id )
        //			where tb.sm_date = ?
        //			and tb.grid_id = ?
        //			and u.school_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        $sqlBackup = "SELECT IFNULL(sum( tb.done_qty ), 0) AS total
                    FROM tehillim_backups tb
                    JOIN users u using ( user_id ) 
                    WHERE tb.sm_date = ? 
                    AND tb.grid_id = ?
                    AND u.school_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        foreach ($this->rDates as $month => $date) {

            foreach ($this->tasks as $key => $task) {

                if (!$minutes && $key == 'Minutes') continue;

                // figure out if we are getting results from backup table or not
//				$stmtQuota->execute( array( $date, $task, $id ) );
//				$rowQuota = $stmtQuota->fetch( PDO::FETCH_ASSOC );
//				if ($rowQuota['total'] > 0) {
//                    $this->schoolResults[$key][$date] = $rowQuota['total'];
//				} else {
                $stmt1->execute(array($id, $date, $date, $task));
                $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                $this->schoolResults[$key][$date] = $row1['total'];
                //}

                // figure out if we are getting results from backup table or not
                $stmtBackup->execute(array($date, $task, $id));
                $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
                if ($rowBackup['total'] > 0) {
                    $this->schoolDoneResults[$key][$date] = $rowBackup['total'];
                } else {
                    $stmt2->execute(array($date, $date, $task, $id));
                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $this->schoolDoneResults[$key][$date] = $row2['total'];
                }
            }
        }
    }

    public function setASR($date, $col = false)
    {
        $sql = "select school_id, school_name from schools 
                join school_subjects s using (school_id) 
                where school_era is null 
                and s.subject_id = 1 
                and schools.chayolei = 1 
                and schools.test_school = 0                   
                and school_id not in (82, 612)";
        if ($col) $sql .= " and col_show = 1";
        $sql .= " order by school_name";
        $rows = $this->db->query($sql);
        foreach ($rows as $row) {
            $this->schools[$row['school_id']] = $row['school_name'];
            $this->setArmySchoolsResults($row['school_id'], $date);
            $this->setArmyDoneSchoolsResults($row['school_id'], $date);
        }
        if ($this->debug) {
            echo "<pre>";
            print_r($this->armySchoolsResults);
            print_r($this->armySchoolsDoneResults);
            echo "</pre>";
        }
        $this->setArmyResultsOrdered();
        $this->setAccomplishedRows();
    }

    public function getSchools()
    {
        return $this->schools;
    }

    private function setArmySchoolsResults($id, $date)
    {

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
                AND dt.grid_id = ?   
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0
                AND ut.enrolled =1
				AND u.user_id = ut.user_id
				AND u.lang_id = dtm.lang_id";

        $stmt1 = $this->db->prepare($sql1);

        //$sqlQuota = "select sum( tb.quota ) as total
        //			from tehillim_backups tb
        //			JOIN users u using ( user_id )
        //			where tb.sm_date = ?
        //			and tb.grid_id = ?
        //			and u.school_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        foreach ($this->tasks as $key => $task) {
            //if ($key == 'Minutes') continue;
            // figure out if we are getting results from backup table or not
            //$stmtQuota->execute( array( $date, $task, $id ) );
            //$rowQuota = $stmtQuota->fetch( PDO::FETCH_ASSOC );
            //if ($rowQuota['total'] > 0) {
            //	$this->armySchoolsResults[$key][$id] = $rowQuota['total'];
            //} else {
            $stmt1->execute(array($id, $date, $date, $task));
            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
            $this->armySchoolsResults[$key][$id] = $row1['total'] > 0 ? $row1['total'] : 0;
            //}
        }

        $sql2 = "select count(*) as total from users u 
				join user_tracks ut using (user_id) 
				where school_id = " . $id . "  
				and user_registered > 0 
				and ut.subject_id = 1 
				and ut.enrolled = 1";
        $stmt2 = $this->db->query($sql2);
        $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        $this->totalUsers[$id] = $row2['total'];
        if (!isset($this->totalUsers[$id])) $this->totalUsers[$id] = 0;
    }

    private function setArmyDoneSchoolsResults($id, $date)
    {
        /*
        $sql2 = "SELECT sum( dtm.done_qty ) AS total 
                FROM date_tasks_marks dtm 
                JOIN users u 
                USING (user_id) 
                JOIN date_tasks dt 
                USING (date_task_id) 
                JOIN date_tasks_missions dtmm 
                USING ( date_tasks_mission_id )
				JOIN user_tracks ut using (subject_id, track_id, level) 
                WHERE u.school_id = ?     
                AND dtmm.subject_id =1
                AND dtmm.start_date = ?  
                AND dtmm.end_date = ?  
                AND dt.grid_id = ?   
                AND dtmm.school_type_id = u.school_type_id
				AND u.user_id = ut.user_id
				AND u.lang_id = dtmm.lang_id";
		*/
        /*
		$sql2 = "SELECT sum( dtm.done_qty ) AS total 
                FROM date_tasks_marks dtm 
                JOIN users u 
                USING (user_id) 
                JOIN date_tasks dt 
                USING (date_task_id) 
                JOIN date_tasks_missions dtmm 
                USING ( date_tasks_mission_id )
				JOIN user_tracks ut using (subject_id, track_id, level) 
                WHERE u.school_id = ?     
                AND dtmm.subject_id =1
                AND dtmm.start_date = ?  
                AND dtmm.end_date = ?  
                AND dt.grid_id = ?   
                AND dtmm.school_type_id = u.school_type_id
				AND u.user_id = ut.user_id";
        */
        $sql2 = "SELECT sum( dtm.done_qty ) AS total 
                from date_tasks_marks dtm 
                join date_tasks dt using (date_task_id) 
                join date_tasks_missions dtmm using (date_tasks_mission_id) 
                join users u using (user_id) 
                where u.school_id = ? 
                and dtmm.start_date = ?
                and dtmm.end_date = ?
                and dt.grid_id = ?";

        $stmt2 = $this->db->prepare($sql2);

        $sqlBackup = "SELECT IFNULL(sum( tb.done_qty ), 0) AS total
                    FROM tehillim_backups tb
                    JOIN users u using ( user_id ) 
                    WHERE tb.sm_date = ? 
                    AND tb.grid_id = ?
                    AND u.school_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        foreach ($this->tasks as $key => $task) {
            //echo "ID: " . $id . " Date: " . $date . " Task: " . $task . "<br />" . $sql2 . "<br />"; 
            //if ($key == 'Minutes') continue;

            // figure out if we are getting results from backup table or not
            $stmtBackup->execute(array($date, $task, $id));
            $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
            if ($rowBackup['total'] > 0) {
                $this->armySchoolsDoneResults[$key][$id] = $rowBackup['total'];
            } else {
                if (isset($this->armySchoolsResults[$key][$id]) && $this->armySchoolsResults[$key][$id] > 0) {
                    $stmt2->execute(array($id, $date, $date, $task));
                    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $this->armySchoolsDoneResults[$key][$id] = $row2['total'];
                } else {
                    $this->armySchoolsDoneResults[$key][$id] = 0;
                }
            }
        }
    }

    private function setArmyResultsOrdered()
    {
        foreach ($this->armySchoolsResults as $key => $info) {
            foreach ($info as $id => $total) {
                if ($this->armySchoolsResults[$key][$id] == 0) continue;
                $this->armyResultsOrdered[$key][$id] =
                    round(($this->armySchoolsDoneResults[$key][$id] / $this->armySchoolsResults[$key][$id]) * 100, 1);
            }
            //sort array
            arsort($this->armyResultsOrdered[$key]);
        }
    }

    private function setAccomplishedRows()
    {
        foreach ($this->armyResultsOrdered as $key => $info) {
            foreach ($info as $id => $value) {
                if ($id == 61) continue;
                $name = $this->schools[$id];
                $this->accomplishedRows[$key][$name][$this->armySchoolsResults[$key][$id]] =
                    is_null($this->armySchoolsDoneResults[$key][$id]) ? 0 :
                        (int)$this->armySchoolsDoneResults[$key][$id];
            }
            if ($key == 'Kapitelach') {
                $this->accomplishedKapitelach = $this->accomplishedRows[$key];
                $this->pieKapitelach = $this->armySchoolsResults[$key];
            } else if ($key == 'Minutes') {
                $this->accomplishedMinutes = $this->accomplishedRows[$key];
                $this->pieMinutes = $this->armySchoolsResults[$key];
            }
        }
    }

    public function getAccomplishedKapitelach()
    {
        return $this->accomplishedKapitelach;
    }

    public function getAccomplishedMinutes()
    {
        return $this->accomplishedMinutes;
    }

    public function getPieKapitelach()
    {
        $temp = array();
        foreach ($this->pieKapitelach as $id => $value) {
            $this->setSchool($id);
            $name = $this->getSchoolName();
            $temp[$name] = $value;
        }
        return $temp;
    }

    public function getPieMinutes()
    {
        return $this->pieMinutes;
    }

    public function getArmyResultsOrdered()
    {
        return $this->armyResultsOrdered;
    }

    public function getSchoolResults()
    {
        return $this->schoolResults;
    }

    public function getSchoolDoneResults()
    {
        return $this->schoolDoneResults;
    }

    public function setClasses($sid = 0)
    {
        $this->classes = array();
        $sql = "SELECT DISTINCT c . *
                FROM classes c
                JOIN schools s
                USING ( school_id )
                JOIN users u
                USING ( class_id )
                WHERE u.user_registered >0
                AND c.class_era =0";
        if ($sid) {
            $sql .= " and s.school_id = " . $sid;
        } else {
            $sql .= " and s.school_id = " . $this->school_id;
        }
        $sql .= " ORDER BY c.class_grade, c.class_sub";
        $res = $this->db->query($sql);
        if ($res) {
            foreach ($res as $row) {
                $this->classes[$row['class_id']]['grade'] =
                    $row['class_sub'] == "" ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
                $this->classes[$row['class_id']]['teacher'] = $row['class_teacher'];
            }
        }
    }

    /**
     * $sm->setWhatsappClasses
     *
     * sets the interal classes array and returns a list of class_id's sorted by grade
     *
     * @param string $gender
     * @return array
     */
    public function setWhatsappClasses($gender)
    {
        $this->classes = [];
        $gradeInfo = [];
        $grades = ['Pre1a', '1', '2', '3', '4', '5', '6', '7', '8']; // all the grades we support for this report
        // for each grade get the class info
        foreach ($grades as $grade) {
            $sql = "SELECT DISTINCT c . *
					FROM classes c
					JOIN users u
					USING ( class_id )
					JOIN schools s
					ON ( s.school_id = u.school_id )
					WHERE u.user_registered > 0 
					AND c.class_era = 0
					AND c.class_grade = '" . $grade . "'
					AND c.class_gender = '" . $gender . "' 
					AND u.school_id NOT IN (82)
					AND s.tehillim = 1 
					ORDER BY c.class_grade, c.class_sub ";
            //echo $sql . "<br />";
            foreach ($this->db->query($sql) as $row) {
                // set the interal class datastructure to have the grade and teacher below the class_id..
                $this->classes[$row['class_id']]['grade'] =
                    $row['class_sub'] == "" ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
                $this->classes[$row['class_id']]['teacher'] = $row['class_teacher'];
                // add the class id to the grade info
                $gradeInfo[$grade][] = $row['class_id'];
            }
            // count the number of kids in each class
            $sql2 = "SELECT c.class_id, count(u.user_id) AS total FROM users u 
					JOIN user_tracks ut USING (user_id)
					JOIN classes c ON c.class_id = u.class_id 
                    WHERE c.class_grade = '" . $grade . "'
                    AND c.class_gender = '" . $gender . "'  
					AND u.user_registered > 0 
					AND ut.subject_id = 1 
					AND ut.enrolled = 1
					GROUP BY c.class_id";
            foreach ($this->db->query($sql2) as $row2) {
                $this->totalUsers[$row2['class_id']] = $row2['total'];
                if (!isset($this->totalUsers[$row2['class_id']])) $this->totalUsers[$row2['class_id']] = 0;
            }
        }
        return $gradeInfo;
    }

    public function setClassResults($setClasses = true)
    {
        // determine if we should set the interal classes array as well
        if ($setClasses) $this->setClasses();

        $sql1 = "SELECT sum( dt.quantity ) AS total
                FROM date_tasks dt
                JOIN date_tasks_missions dtm USING ( date_tasks_mission_id )
                JOIN user_tracks ut USING ( track_id, LEVEL , subject_id )
                JOIN users u USING ( user_id )
                JOIN classes c ON ( c.class_id = u.class_id )
                WHERE u.class_id = ?  
                AND ut.subject_id = 1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND dt.grid_id = ? 
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0
                AND ut.enrolled =1
				AND u.user_id = ut.user_id
				AND u.lang_id = dtm.lang_id";

        $stmt1 = $this->db->prepare($sql1);

        $sql2 = "SELECT sum( dtm.done_qty ) AS total
                FROM date_tasks_marks dtm 
                JOIN date_tasks dt USING (date_task_id) 
                JOIN date_tasks_missions dtmm USING (date_tasks_mission_id) 
                JOIN users u USING (user_id) 
                JOIN classes c USING (class_id) 
                WHERE dtmm.start_date = ?
                AND dtmm.end_date = ?
                AND dt.grid_id = ?  
                AND c.class_id = ?  
                AND u.user_registered > 0";

        $stmt2 = $this->db->prepare($sql2);

        //$sqlQuota = "select sum( tb.quota ) as total
        //			from tehillim_backups tb
        //			JOIN users u using ( user_id )
        //			where tb.sm_date = ?
        //			and tb.grid_id = ?
        //			and u.class_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        $sqlBackup = "SELECT IFNULL(sum( tb.done_qty ), 0) AS total
                    FROM tehillim_backups tb
                    JOIN users u using ( user_id ) 
                    WHERE tb.sm_date = ? 
                    AND tb.grid_id = ?
                    AND u.class_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        foreach ($this->rDates as $month => $date) {

            foreach ($this->tasks as $key => $task) {
                // do not do the minutes task ( only task #1 )
                if ($setClasses && $key == 'Minutes') continue;

                foreach ($this->classes as $class => $info) {
                    // figure out if we are getting results from backup table or not
                    //$stmtQuota->execute( array( $date, $task, $class ) );
                    //$rowQuota = $stmtQuota->fetch( PDO::FETCH_ASSOC );
                    //if ($rowQuota['total'] > 0) {
                    //	$this->classResults[$key][$date][$class] = $rowQuota['total'];
                    //} else {
                    $stmt1->execute(array($class, $date, $date, $task));
                    $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                    $this->classResults[$key][$date][$class] = $row1['total'];
                    //}
                    // if we do not want to only show what was done but also show the totals
                    if (!$this->accomplishedOnly) {
                        // figure out if we are getting results from backup table or not
                        $stmtBackup->execute(array($date, $task, $class));
                        $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
                        $backupRan = $this->getBackupRan($date);

                        if ($rowBackup['total'] > 0 || $backupRan) {
                            $this->classDoneResults[$key][$date][$class] = $rowBackup['total'];
                        } else {
                            $stmt2->execute(array($date, $date, $task, $class));
                            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                            $this->classDoneResults[$key][$date][$class] = $row2['total'];
                        }

                        //find out which classes accomplished their goals
                        if ($this->classDoneResults[$key][$date][$class] >= $this->classResults[$key][$date][$class]) {
                            $this->accomplished[$key][$date][$info['grade']] = $info['teacher'];
                        }
                    }
                }
            }
        }
    }

    public function setWhatsappStudentResults($class_id)
    {
        $qry = "select * from users where class_id = $class_id and user_registered > 0 order by last, first";
        $stmt = $this->db->query($qry);
        $users[$class_id] = $stmt->fetchAll();

        $sql1 = "SELECT dt.quantity AS total, dt.date_task_id 
                FROM date_tasks dt
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN user_tracks ut
                USING ( track_id,
                LEVEL , subject_id )
                WHERE dtm.subject_id =1
                AND dtm.start_date = ? 
                AND dtm.end_date = ? 
                AND dt.grid_id = ? 
                AND dtm.school_type_id = ?
                AND ut.user_id = ?
				AND dtm.lang_id = ? 
                AND ut.enrolled =1";
        $stmt1 = $this->db->prepare($sql1);

        $sql2 = "select MAX(dtm.done_qty) as total 
				from date_tasks_marks dtm
				join date_tasks dt using (date_task_id)
				join date_tasks_missions dtmm using (date_tasks_mission_id) 
				where dtm.user_id = ? 
				and dtmm.start_date = ? 
				and dtmm.end_date = ? 
				and dt.grid_id = ?";
        $stmt2 = $this->db->prepare($sql2);

        //$sqlQuota = "select sum( tb.quota ) as total
        //			from tehillim_backups tb
        //			where tb.sm_date = ?
        //			and tb.grid_id = ?
        //			and tb.user_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        $sqlBackup = "SELECT IFNULL(sum( tb.done_qty ), 0) AS total
                    FROM tehillim_backups tb
                    WHERE tb.sm_date = ? 
                    AND tb.grid_id = ?
                    AND tb.user_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        foreach ($this->rDates as $month => $date) {

            foreach ($this->tasks as $key => $task) {

                if ($key == 'Minutes') continue;

                $this->doneQuotas[$key][$class_id] = 0;
                $this->participated[$key][$class_id] = 0;

                foreach ($users as $class => $info) {

                    foreach ($info as $user) {

                        // figure out if we are getting results from backup table or not
                        //$stmtQuota->execute( array( $date, $task, $user['user_id'] ) );
                        //$rowQuota = $stmtQuota->fetch( PDO::FETCH_ASSOC );
                        //if ($rowQuota['total'] > 0) {
                        //	$this->studentResults[$date][$class][$user['user_id']][$key] = $rowQuota['total'];
                        //	$row1['total'] = $rowQuota['total']; // needed for later checking
                        //} else {
                        $stmt1->execute(array($date, $date, $task, $user['school_type_id'], $user['user_id'], $user['lang_id']));
                        $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                        $this->studentResults[$date][$class][$user['user_id']][$key] = $row1['total'];
                        //}

                        // figure out if we are getting results from backup table or not
                        $stmtBackup->execute(array($date, $task, $user['user_id']));
                        $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
                        if ($rowBackup['total'] > 0) {
                            $total = $rowBackup['total'];
                            $this->studentDoneResults[$date][$class][$user['user_id']][$key] = $rowBackup['total'];
                        } else {
                            $stmt2->execute(array($user['user_id'], $date, $date, $task));
                            $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                            $total = $row2['total'];
                            $this->studentDoneResults[$date][$class][$user['user_id']][$key] = $row2['total'];
                        }

                        if ($total > 0) {
                            $this->participated[$key][$class_id]++;
                            if ($total >= $row1['total']) {
                                $this->doneQuotas[$key][$class_id]++;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getDoneQuotas()
    {
        return $this->doneQuotas;
    }

    public function getTotalUsers()
    {
        return $this->totalUsers;
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getClassResults()
    {
        return $this->classResults;
    }

    public function getClassDoneResults()
    {
        return $this->classDoneResults;
    }

    public function getAccomplished()
    {
        return $this->accomplished;
    }

    public function showDone($date)
    {
        if (in_array($date, $this->showDone)) {
            return true;
        } else {
            return false;
        }
    }

    public function setStudentResults($sid = 0, $date = 0)
    {
        $dates = [];
        if ($date) $dates[] = $date;
        else {
            foreach ($this->rDates as $date) {
                if ($date <= unixtojd()) $dates[] = $date;
            }
        }

//		$sql1 = "SELECT dt.quantity AS total, dt.date_task_id
//            FROM date_tasks dt
//            JOIN date_tasks_missions dtm
//            USING ( date_tasks_mission_id )
//            JOIN user_tracks ut
//            USING ( track_id, level, subject_id )
//            WHERE dtm.subject_id = 1
//            AND dtm.start_date = ?
//            AND dtm.end_date = ?
//            AND dt.grid_id = ?
//            AND dtm.school_type_id = ?
//            AND ut.user_id = ?
//		    AND dtm.lang_id = ?
//            AND ut.enrolled =1";

//        $sql1 = "SELECT dt.quantity AS total, dt.date_task_id
//            FROM date_tasks dt
//            JOIN date_tasks_missions dtm
//            USING ( date_tasks_mission_id )
//            JOIN user_tracks ut
//            USING ( track_id, level, subject_id )
//            WHERE dtm.subject_id = 1
//            AND dtm.start_date = ?
//            AND dtm.end_date = ?
//            AND dt.grid_id = ?
//            AND dtm.school_type_id = ?
//            AND ut.user_id = ?
//		    AND dtm.lang_id = ?
//            AND ut.enrolled =1";

        $sql1 = "select * from date_tasks 
                where grid_id = ?
                and date_tasks_mission_id in (
                    select date_tasks_mission_id from date_tasks_missions dtm 
                    where dtm.subject_id = 1 
                    and dtm.start_date = ?
                    and dtm.end_date = ?
                    and dtm.school_type_id = ?
                    and dtm.track_id = ?
                    and dtm.level = ?
                    and dtm.lang_id = ?
                )";

        $stmt1 = $this->db->prepare($sql1);

        $sql2 = "SELECT MAX(dtm.done_qty) AS total 
				FROM date_tasks_marks dtm
				JOIN date_tasks dt USING (date_task_id)
				JOIN date_tasks_missions dtmm USING (date_tasks_mission_id) 
				WHERE dtm.user_id = ? 
				AND dtmm.start_date = ? 
				AND dtmm.end_date = ? 
				AND dt.grid_id = ? 
				AND dtmm.subject_id = 1";
        /*
            $sql2 = "SELECT sum( dtm.done_qty ) AS total
                    FROM users u
                    JOIN (
                    date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
                    ) ON ( dtm.user_id = u.user_id
                    AND dt.date_task_id = dtm.date_task_id
                    AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
                    AND dtmm.start_date = ?
                    AND dtmm.end_date = ?
                    AND dt.grid_id = ? )
                    WHERE u.user_id = ?";
            */

        $stmt2 = $this->db->prepare($sql2);

        //$sqlQuota = "select sum( quota ) as total
        //			from tehillim_backups
        //			where sm_date = ?
        //			and grid_id = ?
        //			and user_id = ?";
        //$stmtQuota = $this->db->prepare( $sqlQuota );

        $sqlBackup = "SELECT IFNULL(sum( done_qty ), 0) AS total
            FROM tehillim_backups 
            WHERE sm_date = ? 
            AND grid_id = ?
            AND user_id = ?";
        $stmtBackup = $this->db->prepare($sqlBackup);

        if ($sid) {
            $this->classes = array();
            $this->setClasses($sid);
        } else if (empty($this->classes)) {
            $this->setClasses($sid);
        }

        if (!$sid) $sid = $this->school_id;

        $users = array();
        foreach ($this->classes as $id => $info) {
            $stmt = $this->db->query("
                SELECT u.*, ut.track_id, ut.level FROM users u 
                join user_tracks ut using (user_id) 
                WHERE class_id = $id 
                AND user_registered > 0 
                AND ut.subject_id = 1 
                ORDER BY last, first");
            $users[$id] = $stmt->fetchAll();
        }

        foreach ($users as $info) {
            foreach ($info as $user) {
                $this->users[$user['user_id']] = $user['first'] . ' ' . $user['last'];
            }
        }

        // cache results
        $cached = [];

        foreach ($dates as $date) {
            // for each task
            foreach ($this->tasks as $key => $task) {
                // skip task #2
                // if ($key == 'Minutes') continue;
                $this->doneQuotas[$key][$sid] = 0;
                $this->participated[$key][$sid] = 0;
                // for each class
                foreach ($users as $class => $info) {
                    //if ($sid == 176) echo $month . ":" . $date . ":" . $key . ":" . $sid . ":" . count($info) . "<br />";
                    // for each user in the class.
                    foreach ($info as $user) {
                        if (isset($cached[$task][$date][$user['school_type_id']][$user['track_id']][$user['level']][$user['lang_id']]))
                            $this->studentResults[$date][$class][$user['user_id']][$key] = $cached[$task][$date][$user['school_type_id']][$user['track_id']][$user['level']][$user['lang_id']];
                        else {
                            $stmt1->execute([$task, $date, $date, $user['school_type_id'], $user['track_id'], $user['level'], $user['lang_id']]);
                            $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
                            $this->studentResults[$date][$class][$user['user_id']][$key] = $row1['quantity'];
                            $cached[$task][$date][$user['school_type_id']][$user['track_id']][$user['level']][$user['lang_id']] = $row1['quantity'];
                        }

                        // figure out if we are getting results from backup table or not
                        $stmtBackup->execute(array($date, $task, $user['user_id']));
                        $rowBackup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
                        // check if we have run the backup for this date
                        $backupRan = $this->getBackupRan($date);

                        if ($rowBackup['total'] > 0 || $backupRan) { // or we are after the date of the backup.
                            $total = $rowBackup['total'] > 0 ? $rowBackup['total'] : 0;
                            $this->studentDoneResults[$date][$class][$user['user_id']][$key] = $rowBackup['total'] > 0 ? $rowBackup['total'] : 0;
                        } else {
                            if (isset($this->studentResults[$date][$class][$user['user_id']][$key]) &&
                                $this->studentResults[$date][$class][$user['user_id']][$key] > 0) {
                                $stmt2->execute(array($user['user_id'], $date, $date, $task));
                                $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                                $total = $row2['total'];
                                $this->studentDoneResults[$date][$class][$user['user_id']][$key] = $row2['total'];
                            } else {
                                $this->studentDoneResults[$date][$class][$user['user_id']][$key] = 0;
                            }
                        }

                        if ($sid && $total > 0) {
                            $this->participated[$key][$sid]++;
                            if ($total >= $row1['quantity']) {
                                $this->doneQuotas[$key][$sid]++;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getStudentResults()
    {
        return $this->studentResults;
    }

    public function getStudentDoneResults()
    {
        return $this->studentDoneResults;
    }

    public function generateArmyTable($month, $date)
    {
        ?>

      <table>
        <tr>
          <th>Army Wide Goal</th>
            <? if ($this->showDone($date)) { ?>
              <th>Army Wide Accomplishment</th>
            <? } ?>
        </tr>

          <? foreach ($this->tasks as $key => $task) {
              if ($key == 'Minutes') continue; ?>
            <tr>
              <td><?= number_format($this->armyResults[$key][$date]) ?> <?= $key ?></td>
                <? if ($this->showDone($date)) { ?>
                  <td><?= number_format($this->armyDoneResults[$key][$date]) ?> <?= $key ?></td>
                <? } ?>
            </tr>
          <? } ?>
      </table>

        <?
    }

    public function generateBaseTable($month, $date)
    {
        ?>

      <table>
        <tr>
          <th>Base Goal</th>
            <? if ($this->showDone($date)) { ?>
              <th>Base Accomplishment</th>
            <? } ?>
        </tr>

          <? foreach ($this->tasks as $key => $task) {
              if ($key == 'Minutes') continue; ?>
            <tr>
              <td><?= number_format($this->schoolResults[$key][$date]) ?> <?= $key ?></td>
                <? if ($this->showDone($date)) { ?>
                  <td><?= number_format($this->schoolDoneResults[$key][$date]) ?> <?= $key ?></td>
                <? } ?>
            </tr>
          <? } ?>
      </table>

        <?
    }

    public function generateSummary($month, $date)
    {
        $first = true;
        ?>

      <table>
      <tr>
        <th>Platoon</th>
        <th>Teacher</th>
        <th>Platoon Goal</th>
          <? if ($this->showDone($date)) { ?>
            <th>Platoon Accomplishment</th>
            <th>More than Goal</th>
            <th>Less than Goal</th>
          <? } ?>
      </tr>

        <? foreach ($this->classes as $class_id => $class) { ?>

        <?
        $grade = substr($class['grade'], 0, 1);
        $sub = substr($class['grade'], 2);
        if ($this->school_id == 54 && $grade == 4 && $sub == 420 && $first) {
            $first = false;
            ?>
          </table>
          <br/><br/>
          <div class="page-break"></div>
          <table>
          <tr>
            <th>Platoon</th>
            <th>Teacher</th>
            <th>Platoon Goal</th>
              <? if ($this->showDone($date)) { ?>
                <th>Platoon Accomplishment</th>
                <th>More than Goal</th>
                <th>Less than Goal</th>
              <? } ?>
          </tr>
            <?
        }
        ?>

        <? foreach ($this->tasks as $key => $task) {
            if ($key == 'Minutes') continue; ?>

        <tr>
          <td><?= $class['grade'] ?></td>
          <td>
              <?php
              if (preg_match('/[א-ת]/',$class['teacher'])) echo "<span class='hebrewFont'>" . $class['teacher'] . "</span>";
              else echo $class['teacher'];
              ?>
          </td>
          <td><?= number_format($this->classResults[$key][$date][$class_id]) ?> <?= $key ?></td>
            <? if ($this->showDone($date)) { ?>
              <td><?= number_format($this->classDoneResults[$key][$date][$class_id]) ?> <?= $key ?></td>
                <? if ($this->classDoneResults[$key][$date][$class_id] > $this->classResults[$key][$date][$class_id]) { ?>
                <td><?= ($this->classDoneResults[$key][$date][$class_id] - $this->classResults[$key][$date][$class_id]) ?> <?= $key ?></td>
                <td>&nbsp;</td>
                <? } else if ($this->classDoneResults[$key][$date][$class_id] < $this->classResults[$key][$date][$class_id]) { ?>
                <td>&nbsp;</td>
                <td><?= ($this->classResults[$key][$date][$class_id] - $this->classDoneResults[$key][$date][$class_id]) ?> <?= $key ?></td>
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

    public function generateClassSummary($month, $date)
    {
        ?>

      <table>
        <tr>
          <th>Platoon</th>
          <th>Teacher</th>
          <th>Platoon Goal</th>
        </tr>

          <? foreach ($this->classes as $class_id => $class) { ?>

              <?
              $grade = substr($class['grade'], 0, 1);
              $sub = substr($class['grade'], 2);
              ?>

              <? foreach ($this->tasks as $key => $task) {
                  if ($key == 'Minutes') continue; ?>

              <tr>
                <td><?= $class['grade'] ?></td>
                <td><?= $class['teacher'] ?></td>
                <td><?= number_format($this->classResults[$key][$date][$class_id]) ?> <?= $key ?></td>
              </tr>

              <? } ?>

          <? } ?>

      </table>
        <?
    }

    public function generateReport()
    {

        foreach ($this->classes as $class_id => $class) {
            ?>

          <div class='hayomYom'>
            <p align="right"> ×”×™×•×� ×™×•×� - ×›"×” ×©×‘×˜ </p>
            <p align="right">
              ×’Ö¼×•Ö¹×žÖµ×¨ ×–Ö·×™×™×Ÿ ×“×¢Ö¶×� ×ªÖ¼Ö°×”Ö´×œÖ¼Ö´×™×� ×©×�Ö·×‘Ö¼Ö¸×ª ×žÖ°×‘Ö¸×¨Ö°×›Ö´×™×� - ×“×�Ö¸×¡
              ×“×�Ö·×¨×£ ×ž×¢Ö¶×Ÿ ×�Ö¸×¤Ö¼×”Ö´×™×˜×¢Ö¶×Ÿ, ×“×�Ö¸×¡ ×�Ö´×™×– × ×•Ö¹×’Öµ×¢Ö· ×�Ö´×™×”×�, ×–Ö·×™×™× ×¢Ö¶
              ×§Ö´×™× Ö°×“×¢Ö¶×¨ ×�×•Ö¼×Ÿ ×§Ö´×™× Ö°×“×¡ ×§Ö´×™× Ö°×“×¢Ö¶×¨
            </p>
            <p>
              "One should be careful to say Tehillim everyday and to say the whole Tehillim on Shabbos Mevorchim.
              These things are important for every person, his children and grandchildren."
            </p>
          </div>

          <div>
            Base name: <?= $this->school_name ?><br/>
            Platoon: <?= $class['grade'] ?><br/>
            Teacher: <?= $class['teacher'] ?><br/>
          </div>
          <br/>

            <?
            $date = end($this->rDates);
            $month = key($this->rDates);
            $this->generateBaseTable($month, $date);
            $this->generateArmyTable($month, $date);
            ?>
          <br/>

          <p align='center' style='font-size: 54px'>×©×‘×ª ×ž×‘×¨×›×™×� <?= $this->getHebrewMonth($month) ?></p>
          <table>

              <? if ($this->showDone($date)) { ?>
                <tr>
                  <th>Our Platoon Goal</th>
                  <th>Our Platoon Accomplishment</th>
                  <th>Our Base Goal</th>
                  <th>Our Base Accomplishment</th>
                  <th>Army Wide Goal</th>
                  <th>Army Wide Accomplishment</th>
                </tr>

                  <? foreach ($this->tasks as $key => $task) {
                      if ($key == 'Minutes') continue; ?>
                  <tr>
                    <td><?= number_format($this->classResults[$key][$date][$class_id]) ?> <?= $key ?></td>
                    <td><?= number_format($this->classDoneResults[$key][$date][$class_id]) ?> <?= $key ?></td>
                    <td><?= number_format($this->schoolResults[$key][$date]) ?> <?= $key ?></td>
                    <td><?= number_format($this->schoolDoneResults[$key][$date]) ?> <?= $key ?></td>
                    <td><?= number_format($this->armyResults[$key][$date]) ?> <?= $key ?></td>
                    <td><?= number_format($this->armyDoneResults[$key][$date]) ?> <?= $key ?></td>
                  </tr>
                  <? }
              } else {
                  ?>
                <tr>
                  <th>Our Platoon Goal</th>
                  <th>Our Base Goal</th>
                  <th>Army Wide Goal</th>
                </tr>

                  <? foreach ($this->tasks as $key => $task) {
                      if ($key == 'Minutes') continue; ?>
                  <tr>
                    <td><?= number_format($this->classResults[$key][$date][$class_id]) ?> <?= $key ?></td>
                    <td><?= number_format($this->schoolResults[$key][$date]) ?> <?= $key ?></td>
                    <td><?= number_format($this->armyResults[$key][$date]) ?> <?= $key ?></td>
                  </tr>
                      <?
                  }
              }
              ?>

          </table>
          <br/>
          <div class="page-break"></div>
        <? }
    }

    public function generateAccomplishedReport()
    {

        if (!empty($this->accomplished)) {

            $keys = array_keys($this->tasks);
            ?>

          <h2>Platoons that accomplished their goals</h2>

            <? foreach ($keys as $key) {

                if ($key == 'Minutes') continue;

                if (!empty($this->accomplished[$key])) {

                    if ($key == 'Kapitelach')
                        echo "<div style='float: left'>";
                    else
                        echo "<div style='float: right'>";

                    echo "<p><b>" . $key . "</b></p>";

                    foreach ($this->rDates as $month => $date) {
                        if (array_key_exists($date, $this->accomplished[$key])) { ?>
                          <p>Shabbos Mevorchim <?= $month ?></p>
                          <table>
                            <tr>
                              <th>Grade</th>
                              <th>Teacher</th>
                            </tr>
                              <? foreach ($this->accomplished[$key][$date] as $grade => $teacher) { ?>
                                <tr>
                                  <td><?= $grade ?></td>
                                  <td><?= $teacher ?></td>
                                </tr>
                              <? } ?>
                          </table>
                          <br/>
                        <? }
                    }

                    echo "</div>";

                }
            }
        }
    }

    public function generateArmyAccomplishedReport()
    {
        foreach ($this->armyResultsOrdered as $key => $results) {
            echo "<h3>" . $key . "</h3>";
            ?>
          <br/>
          <table>
            <tr>
              <th>School Name</th>
              <th>Goal</th>
              <th>Accomplished</th>
              <th>More Than Goal</th>
              <th>Less Than Goal</th>
            </tr>
              <?
              foreach ($results as $id => $total) {
                  //$this->setSchool( $id );
                  //$this->setStudentResults($id);
                  $goal = $this->armySchoolsResults[$key][$id];
                  $done = $this->armySchoolsDoneResults[$key][$id];
                  echo "<tr><td>" . $this->schools[$id] . "</td><td>" . $goal .
                      "</td><td>" . $done . " <span class='percent'>(" .
                      $this->armyResultsOrdered[$key][$id] . "%)</span></td>";
                  if ($done > $goal) {
                      echo "<td>" . ($done - $goal) . "</td><td>&nbsp;</td></tr>";
                  } else if ($goal > $done) {
                      echo "<td>&nbsp;</td><td>" . ($goal - $done) . "</td></tr>";
                  } else {
                      echo "<td>&nbsp;</td><td>&nbsp;</td></tr>";
                  }
              }
              ?>
          </table>
          <br/>
          <div class='page-break'></div>
            <?
        }
        //echo "<pre>"; print_r($this->fulfilledQuotas); echo "</pre>"; exit;
    }

    public function generateHQReport($debug = false)
    {
        foreach ($this->armyResultsOrdered as $key => $results) {
            if ($key == 'Minutes') continue;

            // figure out order based on percent of done quotas
            $ordered = array();
            foreach ($results as $id => $total) {
                $quota = round($this->doneQuotas[$key][$id] / $this->totalUsers[$id] * 100.00, 2);
                $ordered[$id] = $quota;
            }
            arsort($ordered);
            if ($debug) {
                echo "<pre>";
                print_r($ordered);
                echo "</pre>";
                exit;
            }

            echo "<h3>" . $key . "</h3>";
            ?>
          <br/>
          <table>
            <tr>
              <th>School Name</th>
              <th># Chayolim Registered for Tehillim</th>
              <th>Goal</th>
              <th>Accomplished</th>
              <th>Percentage of Chayolim Participated</th>
              <th>Percentage of Chayolim Completed their Quota</th>
              <th>Average Kapitelach per Chayol</th>
              <th>Average Minutes per Chayol</th>
            </tr>
              <?
              foreach ($ordered as $id => $quota) {
                  //$this->setStudentResults($id);
                  $goal = $this->armySchoolsResults[$key][$id];
                  $done = $this->armySchoolsDoneResults[$key][$id] ? $this->armySchoolsDoneResults[$key][$id] : 0;
                  $minutesDone = $this->armySchoolsDoneResults['Minutes'][$id] ? $this->armySchoolsDoneResults['Minutes'][$id] : 0;
                  echo "<tr><td>" . $this->schools[$id] . "</td><td>" . $this->totalUsers[$id] . "</td><td>" .
                      $goal . "</td><td>" . $done . " <span class='percent'>(" .
                      $this->armyResultsOrdered[$key][$id] . "%)</span></td><td>" .
                      round($this->participated[$key][$id] / $this->totalUsers[$id] * 100.00) . "%</td><td>" .
                      $quota . "%</td><td>" .
                      round($done / $this->totalUsers[$id]) . "</td><td>" .
                      round($minutesDone / $this->totalUsers[$id]) . "</td></tr>";;
              }
              ?>
          </table>
          <br/>
          <div class='page-break'></div>
            <?
        }
        //echo "<pre>"; print_r($this->fulfilledQuotas); echo "</pre>"; exit;
    }

    public function generateUsersReport()
    {
        foreach ($this->usersResults as $class => $users) {
            foreach ($users as $user => $other) {
                echo "<h2>" . $this->classes[$class]['grade'] . ' - ' . $this->users[$user] . "</h2>";
                echo "<div align='center'>";
                foreach ($other as $date => $info) {
                    echo "<div class='user'>";
                    echo array_search($date, $this->dates) . "<br />";
                    echo "<table>";
                    echo "<tr><th>Goal</th><th>Accomplishment</th></tr>";
                    foreach ($info as $task => $total) {
                        echo "<tr><td>" . $total . ' ' . $task . "</td><td>" .
                            (isset($this->usersDoneResults[$class][$user][$date][$task]) ?
                                $this->usersDoneResults[$class][$user][$date][$task] . ' ' . $task : ' ') .
                            "</td></tr>";
                    }
                    echo "</table></div>";
                }
                echo "<div style='clear: both'></div></div>";
                echo "<div class='page-break'></div>";
            }
        }
    }

    public function generateStudentReport()
    {
        //echo "<pre>"; print_r($this->studentResults); echo "</pre>";
        //echo "<pre>"; print_r($this->studentDoneResults); echo "</pre>"; return;
        foreach ($this->studentResults as $date => $info) {
            if ($date > unixtojd()) $future = true;
            else $future = false;
            foreach ($info as $class => $users) {
                $totals = array();
                echo "<h2>" . $this->school_name . ' ' . $this->classes[$class]['grade'] . "</h2>";
                echo "<div align='center'>";
                echo "<span class='hebrewDate'>" . $this->getHebrewMonth(array_search($date, $this->dates)) . "</span><br />";
                echo "<table>";
                echo "<tr><th>Chayol</th><th>Goal</th>";
                if (!$future) echo "<th>Accomplishment</th>";
                echo "<th>Minutes Goal</th>";
                if (!$future) echo "<th>Minutes Accomplishment</th>";
                echo "</tr>";
                foreach ($users as $user => $info) {
                    echo "<tr><td>" . $this->users[$user] . "</td>";
                    foreach ($info as $task => $total) {
                        if (isset($totals[$task]['goal'])) {
                            $totals[$task]['goal'] += $total;
                        } else {
                            $totals[$task]['goal'] = $total;
                        }
                        echo "<td>" . $total . ' ' . $task . "</td>";
                        if (!$future) {
                            echo "<td>";
                            if (isset($this->studentDoneResults[$date][$class][$user][$task])) {
                                echo $this->studentDoneResults[$date][$class][$user][$task] . ' ' . $task;
                                if (isset($totals[$task]['done'])) {
                                    $totals[$task]['done'] += $this->studentDoneResults[$date][$class][$user][$task];
                                } else {
                                    $totals[$task]['done'] = $this->studentDoneResults[$date][$class][$user][$task];
                                }
                            } else {
                                echo "0 " . $task;
                                if (isset($totals[$task]['done'])) {
                                    $totals[$task]['done'] += 0;
                                } else {
                                    $totals[$task]['done'] = 0;
                                }
                                echo "&nbsp;";
                            }
                            echo "</td>";
                        }
                    }
                    echo "</tr>";
                }
                echo "<tr><td align='right'>Totals:</td>";
                foreach ($totals as $type => $task) {
                    echo "<td>" . $task['goal'] . ' ' . $type . "</td>";
                    if (!$future) echo "<td>" . $task['done'] . ' ' . $type . "</td>";
                }
                echo "</tr>";
                echo "</table></div>";
                echo "<div class='page-break'></div>";
            }
        }
    }
}

?>