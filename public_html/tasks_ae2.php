<?
$updated=0;
$created=0;
$deleted=0;
if(!isset($task_ae_table)) $task_ae_table = 'tasks';

foreach(gra('tasks') as $id => $data):
  $id = intval($id);

  if($id>=0) {
    if(!verify_task_id($id)) continue; //security check for permission to edit this task
    if(!empty($data['delete'])) {
      $deleted++;
      mq("DELETE FROM marks WHERE task_id = $id");
      mq("DELETE FROM $task_ae_table WHERE task_id = $id");
      continue;
    } else {
      $sql = "UPDATE $task_ae_table";
      $updated++;
    }
    $sql2 = " WHERE task_id = $id";
  } elseif(!empty($data['include'])) {
    $sql = "INSERT INTO $task_ae_table";
    $sql2 = '';
    $created++;
  } else {
    continue;
  }

  $name = agr($data, 'name');
  $repeat = agr($data, 'repeat');
  $start_date_day = agri($data, 'start_date_day');
  $start_date_month = agri($data, 'start_date_month');
  $start_date_year = agri($data, 'start_date_year');
  $end_date_day = agri($data, 'end_date_day');
  $end_date_month = agri($data, 'end_date_month');
  $end_date_year = agri($data, 'end_date_year');
  $every = agri($data, 'every');
  $on_date_day = agri($data, 'on_date_day', 0);
  $on_date_month = agri($data, 'on_date_month', 0);
  $on_sunday = agr($data, 'on_sunday');
  $on_monday = agr($data, 'on_monday');
  $on_tuesday = agr($data, 'on_tuesday');
  $on_wednesday = agr($data, 'on_wednesday');
  $on_thursday = agr($data, 'on_thursday');
  $on_friday = agr($data, 'on_friday');
  $on_shabbos = agr($data, 'on_shabbos');

  $sql .= " SET $owner_sql, name = " . ms($name);

  ($start_date = jewishtojd($start_date_month, $start_date_day, $start_date_year)) || trigger_error('error 3', E_USER_ERROR);

  $sql .= ", start_date = $start_date";

  if($repeat == 'once') {
    $sql .= ", end_date = $start_date";
    $sql .= ", every = 1";
  } else {
    if(!empty($end_date_year) && !empty($end_date_month) && !empty($end_date_day)) {
      ($end_date = jewishtojd($end_date_month, $end_date_day, $end_date_year)) || trigger_error('error 4', E_USER_ERROR);
      $sql .= ", end_date = $end_date";
    }

    $sql .= ", every = $every";
  }

  switch($repeat) {
    case 'once':
      $repeat = 'daily';
    case 'daily':
      break;
    case 'weekly':
      $sql .= ", rep_param1 = " . (
        0
        | (empty($on_sunday) ? '0' : '1') << 0
        | (empty($on_monday) ? '0' : '1') << 1
        | (empty($on_tuesday) ? '0' : '1') << 2
        | (empty($on_wednesday) ? '0' : '1') << 3
        | (empty($on_thursday) ? '0' : '1') << 4
        | (empty($on_friday) ? '0' : '1') << 5
        | (empty($on_shabbos) ? '0' : '1') << 6
      );
      break;
    case 'yearly':
      if($on_date_month <= 0 || $on_date_month > 13) trigger_error('error 5', E_USER_ERROR);
      $sql .= ", rep_param2 = $on_date_month";
    case 'monthly_date':
      if($on_date_day <= 0 || $on_date_day > 30) trigger_error('error 6', E_USER_ERROR);
      $sql .= ", rep_param1 = $on_date_day";
      break;
    default:
      trigger_error('error 7', E_USER_ERROR);
      break;
  }

  $sql .= ', rep_type = ' . ms($repeat);

  $sql .= $sql2;

  mq($sql);

endforeach;
$message = ($created ? sprintf(T_('%s task(s) created.'), $created) : '') . ' ' . ($updated ? sprintf(T_('%s task(s) updated.'), $updated) : '') . ' ' . ($deleted ? sprintf(T_('%s task(s) deleted.'), $deleted) : '');
?>
