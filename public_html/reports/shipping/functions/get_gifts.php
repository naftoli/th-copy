<?php

/******************** GET_GIFTS() FUNCTION ********************/
/*
 * This function wraps the get_winners_dates function and normalizes the data for the unified shipping report
 * Normalizaition pattern
 *   shipped => is in get_shipped_marks?
 *   item => Tehillim
 *   ajax => gift:<type>:<id>
 *   shipment => the name of the shipment that it is a part of
 *   shipment_id => the id of the shipment (for the dropdown)
 * Notes:
 *  nested by school id and user id for quick access
 */

require_once($_SERVER["DOCUMENT_ROOT"].'/yearly_prize/functions/get_shipped_marks.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/yearly_prize/functions/get_staff.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/yearly_prize/functions/get_students.php');

function get_gifts($school_id, $start_date, $end_date, $debug){
    $gifts_shipped = get_shipped_marks(true);
    $gifts_students = get_students($school_id, "'$start_date'", "'$end_date'", $debug);
    $gifts_staff = get_staff($school_id);
    // shell of a result
    $result = ['students' => [], 'staff' => []];
    // add the students
    foreach($gifts_students as $school_id => $students){
        foreach($students as $student){
            $ajax = "gift:user:".$student['user_id'];
            $shipped = isset($gifts_shipped['user'][$student['user_id']]);
            $result['students'][$school_id][$student['user_id']][] = [
                'shipped' => $shipped,
                'shipment' => $shipped ? $gifts_shipped['user'][$student['user_id']]['shipment'] : "",
                'shipment_id' => $shipped ? $gifts_shipped['user'][$student['user_id']]['shipment_id'] : "",
                //'distributed' => $shipped ? $gifts_shipped['user'][$student['user_id']] : "N/A",
                'item' => 'Tehillim',
                'ajax' => $ajax];
        }
    }
    // add the staff
    foreach($gifts_staff as $school_id => $staff){
        foreach($staff as $staff_member) {
            $ajax = "gift:".$staff_member['type'].":".$staff_member['id'];
            $shipped = isset($gifts_shipped[$staff_member['type']][$staff_member['id']]);
            
            $result['staff'][$school_id][$staff_member['id']][] = [
                'name' => $staff_member['name'],
                'position' => $staff_member['position'],
                'shipped' => isset($gifts_shipped[$staff_member['type']][$staff_member['id']]),
                'shipment' => $shipped ? $gifts_shipped[$staff_member['type']][$staff_member['id']]['shipment'] : "",
                'shipment_id' => $shipped ? $gifts_shipped[$staff_member['type']][$staff_member['id']]['shipment_id'] : "",
                'item' => 'Tehillim',
                'ajax' => $ajax];
        }
    }
    return $result;
}