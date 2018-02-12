<?php 

// MT

// Global includes
include 'globalDefs.php';
  
// Application-specific
include 'pageContent.php';
include 'camp.php'; // ***** //

include 'global_campaigns.php';

include 'camp_campaigns.php';
include 'camp_missions.php';
include 'camp_tasks.php';

include 'camp_marking.php';
//include 'camp_prizes.php';

include 'campDivision.php';

include 'campMember.php';
include 'campGroup.php';

include 'staff.php';

$availableFunc = array(  

	"deactivate_campaign",
	"get_member_global_campaigns",
	"update_group_task",
	"group_date_tasks",
	"get_group_point_groups",
	"get_group_point_missions",
	"update_camp",
	"update_admin",
	"update_user",
	"get_group_task",
	"assign_group_tasks",
	"install_group_campaign",
	"get_group_missions",
	"scan_voucher",
	"reinstall_prize",
	"uninstall_prize",
	"delete_prize",
	"update_prize",
	"install_prize",
	"get_ranks",
	"remove_divisions",
	"add_new_division",
	"add_new_group_type",
	"get_staff_groups",
	"remove_division",
	"save_division",
	"remove_group_type",
	"save_group_type",
	"get_unassigned_camp_members",
	"get_member_points",
	"remove_group",
	"save_group",
	"add_new_group",
	"generate_new_groups",
	"get_all_group_type_division_groups",
	"install_campaign",
	"get_all_group_type_divisions",
	"remove_group_type_task",
	"remove_division_task",
	"get_staff_assignments",
	"assign_staff_type",
	"get_group_type_task",
	"get_division_task",
	"update_task",
	"get_unassigned_camp_members",
	"remove_staff_member",
	"remove_member_group",
	"get_non_assigned_members",
	"assign_member_group",
	"deassign_staff_group",
	"assign_staff_group",
	"get_staff_members",
	"get_member_groups",
	"get_camp_groups",
	"get_number_of_missions",
	"member_date_tasks",
	"get_group_date_tasks",
	"get_marking_group_members",
	"delete_member_group",
	"set_member_group",
	"get_camp_members",
	"get_all_group_types_divisions_groups",
	"get_group_members",
	"get_group_staff",
	
    // Application Session Management
    "login",
    "logout",
	"register_camp", // ***** //
    "get_camp_id",
	
    // Camp Session Management
    "add_session",
    "delete_session",
    "edit_session",
    "get_session",
    "get_all_sessions",
    
    // Group Type Management
    "get_all_group_types",
    "add_group_type",
    "delete_group_type",
    "edit_group_type",
    
    // Division Management
    "get_all_divisions",
	"get_group_type_divisions",
    "add_division",
    "delete_division",
    "edit_division",
    
    // Group Management
    "get_all_groups",
	"get_division_groups",
    "add_group",
    "delete_group",
    "edit_group",
	"generate_groups",
    
    // Global Campaigns
	"get_global_campaigns",
	
    // ***** Camp Campaign Management ***** //
    "get_camp_campaigns",
    "add_camp_campaign",
    "delete_camp_campaign",
    "edit_camp_campaign",
	
    // ***** Camp Mission Management ***** //
	"get_camp_missions",
    "get_campaign_missions",
    "add_camp_mission",
    "delete_camp_mission",
    "edit_camp_mission",
	
    // ***** Camp Task Management ***** //
	"assign_tasks",
    "get_camp_tasks",
    "add_camp_task",
    "delete_camp_task",
    "edit_camp_task",
    
    // Marking
	"start_marking_session_users",
	"start_marking_session_missions",
	"start_marking_session_member_tasks",
    "get_next_group",
	"get_next_missions",
	"save_member_mark",

    // Prizes
    "get_global_prizes",
    "add_camp_prize",
    "delete_camp_prize",
    "edit_camp_prize",
	
	// Staff
	"get_all_staff",
	"get_all_campers",
	"get_staff_types",
	"get_camper_details",
	"get_staff_details",
	"get_camp_details",
	"get_register_camp_members",
	"get_camp_prizes"

		
); //availableFunc

$camp_id = 4/*$_SESSION['camp_id']*/;

callApplicationInterface();
  
function callApplicationInterface() {
	global $availableFunc;
  
	// Determine the how to the data is passed
	$callInterface = 0;
    
	switch ($_SERVER['REQUEST_METHOD']) {
		case "GET": 		
			if (isset($_GET['action'])) { 
				$callInterface = $_GET;
			}
	    break;
	        
		case "POST":
			if (isset($_POST['action'])) {
				$callInterface = $_POST;
			}
		break;
	}
  
	// Execute the function call
	if ($callInterface != 0) {

		
		
		$funcName = $callInterface['action'];
		$params	= $callInterface['params'];
		
		//Decode the input string to JSON
		$params = json_decode($params);
		$params = explode(",",$params);
		
		// Include the correct file to load the function
		if (in_array($funcName, $availableFunc)) {  
            //echo json_encode($funcName($params));
		    echo $funcName($params);
        }
	}
		
}
?>
