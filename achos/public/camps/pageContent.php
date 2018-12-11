<?php

function getAdminPageContent($params)
{
  $adminPageContent = array(
  
    "pageTitle" => "Camps - Tzivos Hashem Management System",
    
    "adminMenuContent" => "Menu",
    "adminMenuItem1Title" => "Dashboard",
    "adminMenuItem1SubItem1Content" => "My Profile",
    "adminSliderContent" => "Dashboard",
    "adminWelcomeLine1" => "Welcome to the Hachayol Admin Dashboard.",
    "adminWelcomeLine2" => "Please select an option to begin.",
    "adminAlertAreaContent" => "Alerts",
    "adminGroupTypesContent" => "Group Types",
    "adminDismissContent" => "x",
    "adminDate" => "May 25",
    "adminAlert1" => "<li><a class=\"dismiss\" href=\"#\" title=\"Dismiss Alert\">x</a>
											<span class=\"date\">May 25</span>
											PLEASE NOTE: This page is best viewed in Firefox or Chrome.
										  </li>",
    "adminAlert2" => "<li><a class=\"dismiss\" href=\"#\" title=\"Dismiss Alert\">x</a>
				                <span class=\"date\">May 25</span><a href=\"#\">5 Staff Members</a> 
                        do not have access privileges.</li>",
    "adminAlert3" => "<li><a class=\"dismiss\" href=\"#\" title=\"Dismiss Alert\">x</a>
                       <span class=\"date\">May 25</span>
										   <a href=\"#\">4 Campers</a> have not been placed in bunks.
									     </li>",

    "adminMenuItem2Content" => "Points",
    "adminMenuItem2SubItem1Content" => "Achievements",
    "adminMenuItem2SubItem2Content" => "Print Cards",
    "adminMenuItem2SubItem3Content" => "Create Cards",
    
    "adminMenuItem3Content" => "PrintCenter",
    
    "adminMenuItem4Content" => "PrintCenter",
    "adminMenuItem4SubMenuItem1Content" => "Print Mission",
    "adminMenuItem4SubMenuItem2Content" => "Print Cards",
    
    "adminMenuItem5Content" => "Control Panel",
    "adminMenuItem5SubMenuItem1Content" => "Install",
    "adminMenuItem5SubMenuItem2Content" => "Competitions",
    "adminMenuItem5SubMenuItem3Content" => "Group Types",
    "adminMenuItem5SubMenuItem4Content" => "Divisions",
    "adminMenuItem5SubMenuItem5Content" => "Groups",
    "adminMenuItem5SubMenuItem6Content" => "Campers",
    "adminMenuItem5SubMenuItem7Content" => "Manage Missions",
    "adminMenuItem5SubMenuItem7Content" => "Manage Staff",
    "adminMenuItem5SubMenuItem8Content" => "Manage Trips",
    "adminMenuItem5SubMenuItem9Content" => "Manage Store",
    "adminMenuItem5SubMenuItem10Content" => "Getting Started",
    
    "adminMenuItem6Content" => "Shop TH"
  );

  return $adminPageContent;
}

?>