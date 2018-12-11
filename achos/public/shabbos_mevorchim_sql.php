<? 
function get_army_sql($task, $date) { 
	$sql = "
		SELECT sum( dt.quantity ) AS total
		FROM date_tasks dt
		JOIN date_tasks_missions dtm
		USING ( date_tasks_mission_id )
		JOIN user_tracks ut
		USING ( track_id,
		LEVEL , subject_id )
		JOIN users u
		USING ( user_id )
		WHERE ut.subject_id =1 ";
	if (is_array($date)) 
		$sql .= "
			AND dtm.start_date in (" . implode(",", $date) . ") 
			AND dtm.end_date in (" . implode(",", $date) . ") ";
	else 
		$sql .= "
			AND dtm.start_date = $date
			AND dtm.end_date = $date ";
	$sql .= "
		AND dt.name = " . $task . " 
		AND dtm.school_type_id = u.school_type_id
		AND u.user_registered > 0
		AND ut.enrolled =1";
	return $sql;
}

function get_army_done_sql($task, $date) { 
	$sql = " 
		SELECT sum( dt.done_qty ) AS total
		FROM date_tasks_marks dt
		JOIN date_tasks
		USING ( date_task_id )
		JOIN date_tasks_missions dtm
		USING ( date_tasks_mission_id )
		JOIN users u 
		USING ( user_id ) 
		WHERE dtm.subject_id =1
		AND dtm.start_date = $date
		AND dtm.end_date = $date
		AND date_tasks.name = " . $task . " 
		AND dtm.school_type_id = u.school_type_id
		AND u.user_registered >0
		GROUP BY date_tasks.name"; 
	return $sql;
}

function get_school_sql($task, $date, $id) { 
	$sql = "
		SELECT sum( dt.quantity ) AS total
		FROM date_tasks dt
		JOIN date_tasks_missions dtm
		USING ( date_tasks_mission_id )
		JOIN user_tracks ut
		USING ( track_id,
		LEVEL , subject_id) 
		JOIN users u
		USING ( user_id )
		JOIN classes c ON ( c.class_id = u.class_id )
		WHERE u.school_id = $id 
		AND ut.subject_id =1
		AND dtm.start_date = $date
		AND dtm.end_date = $date
		AND dt.name = " . $task . " 
		AND dtm.school_type_id = u.school_type_id
		AND u.user_registered >0
		AND ut.enrolled =1";
	return $sql;
}

function get_school_done_sql($task, $date, $id) { 	
$sql = "
	SELECT sum( dtm.done_qty ) AS total
	FROM users u
	LEFT JOIN (
	date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
	) ON ( dtm.user_id = u.user_id
	AND dt.date_task_id = dtm.date_task_id
	AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
	AND dtmm.start_date = $date
	AND dtmm.end_date = $date
	AND dt.name = " . $task . " ) 
	WHERE u.school_id = $id 
	AND u.user_registered >0
	GROUP BY u.school_id"; 
return $sql;
}

function get_class_sql($task, $date, $class_id) { 
	$sql = "
		SELECT sum( dt.quantity ) AS total
		FROM date_tasks dt
		JOIN date_tasks_missions dtm
		USING ( date_tasks_mission_id )
		JOIN user_tracks ut
		USING ( track_id,
		LEVEL , subject_id )
		JOIN users u
		USING ( user_id )
		JOIN classes c ON ( c.class_id = u.class_id )
		WHERE u.class_id = $class_id 
		AND ut.subject_id =1
		AND dtm.start_date = $date
		AND dtm.end_date = $date
		AND dt.name = " . $task . " 
		AND dtm.school_type_id = u.school_type_id
		AND u.user_registered >0
		AND ut.enrolled =1";
	return $sql;
}

function get_class_done_sql($task, $date, $class_id) {
	$sql = "
		SELECT sum( dtm.done_qty ) AS total
		FROM classes c, users u
		LEFT JOIN (
		date_tasks_marks dtm, date_tasks dt, date_tasks_missions dtmm
		) ON ( dtm.user_id = u.user_id
		AND dt.date_task_id = dtm.date_task_id
		AND dtmm.date_tasks_mission_id = dt.date_tasks_mission_id
		AND dtmm.start_date = $date
		AND dtmm.end_date = $date
		AND dt.name = 'How many Kapitlach did you say?' )
		WHERE u.class_id = c.class_id
		AND c.class_id = $class_id 
		AND u.user_registered >0";
	return $sql;
}
?>