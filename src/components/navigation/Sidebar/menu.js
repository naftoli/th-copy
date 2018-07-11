import React from 'react';
// constants
import { LEGACY_URL } from 'components/constants';
const DEFAULT_USER_TYPES = [ 'HQ', 'BC' ];

/**
 * menuReducer
 * 
 * Reducer for items in menu to limit by type (code) provided
 * 
 * @param {string} user_type The type of user (e.g. HQ, INSTITUTION, BC, PARENT) that we are filtering the menu for
 * @param {Array} defaults Array of defaults in the event that the item does not have a user_types array
 * 
 * @returns {function} Returns a valid reducer to be passed to .reduce with an array as the initilaizer
 */
export const menuReducer = ( user_type, defaults ) => ( filtered = [], item ) => {
  // reduce the items down a bit
  if ( item.items ) {
    item = Object.assign( {}, item, 
      { items: item.items.reduce( menuReducer( user_type, defaults ), [] ) }
    );
  }

  if ( item.user_types && item.user_types.indexOf( user_type ) > -1 ) {
    filtered.push( item );
  } else if ( !item.user_types && defaults.indexOf( user_type ) > -1 ) {
    filtered.push( item );
  }
  return filtered;
}

/**
 * getMenu
 * 
 * returns an array for the user_type provided to get their sidebar menu
 * 
 * @param {string} user_type The type of user to get the menu for 
 */
const getMenu = ( user_type ) => {
  // Default to BC
  user_type = user_type || "BC";
  // Define the shape of the menu
  const menu = [
    {
      label: 'Home', path: '/', user_types: [ 'HQ', 'BC', 'TEACHER' ],
      icon: <img src={`${LEGACY_URL}/images/icon_admin_home.png`} alt="home"/>
    },
    {
      label: 'Base Managment', user_types: [ 'HQ', 'BC', 'TEACHER' ],
      icon: <img src={`${LEGACY_URL}/images/icon_dashboard.png`} alt="base-managment"/>,
      items: [
        {
          label: "Soldiers",
          items: [
            { label: "View / Edit", path: '/users' },
            { label: "Registration", path: '/users/registration' },
            { label: "Rank Cards", path: '/users/cards' },
            { label: "Update Missions", legacy: true, path: '/add_missions.php' },
            { label: "Update Medals", legacy: true, path: '/add_medals.php' },
          ]
        },
        { label: 'Soldiers', user_types: [ 'TEACHER' ], path: '/users' },
        { label: "Platoons", path: '/platoons' },
        { label: "Parents", path: '/parents' },
        { label: "Staff", path: '/staff' },
        {
          label: "Base",
          items: [
            { label: 'View / Edit', path: '/base' },
            { label: 'Settings', path: '/base/settings' },
            { label: 'Transactions', path: '/base/transactions' }
          ]
        },
      ]
    },
    {
      label: "Missions", user_types: [ 'HQ', 'BC', 'TEACHER' ],
      icon: <img src={`${LEGACY_URL}/images/icon_admin_medal.png`} alt="Missions" />,
      items: [
        { label: 'Print Missions', legacy: true, path: '/print_missions2.php', user_types: [ 'HQ', 'BC', 'TEACHER' ] },
        { label: 'Print Summer Missions', legacy: true, path: '/print_missions_summer.php' },
        { label: 'Mark Missions', legacy: true, path: '/mark_missions2.php', user_types: [ 'HQ', 'BC', 'TEACHER' ] },
        { label: 'Mark Yahadus', legacy: true, path: '/sefer_hamitzvos.php' },
        { label: 'Personalize Your Missions', legacy: true, path: '/task_customization.php' },
        { label: 'Add Tasks', legacy: true, path: '/newTask.php' },
        { label: 'Teachers Mission Checklist', legacy: true, path: '/mission_sheets_checklist.php' },
        { label: 'Missions Accomplished Report', legacy: true, path: '/missions_report.php' }
      ]
    },
    {
      label: "Achievement Cards", user_types: [ 'HQ', 'BC', 'TEACHER' ],
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Achievement Cards" />,
      items: [
        { label: 'Add Achievement Task', legacy: true, 
          path: '/newAchievementTasks.php', user_types: [ 'HQ', 'BC', 'TEACHER' ] 
        },
        { label: 'Add / Subtract Points', legacy: true, 
          path: '/manual_points.php', user_types: [ 'HQ', 'BC', 'TEACHER' ], 
        },
      ]
    },
    {
      label: "Chidon",
      icon: <img src={`${LEGACY_URL}/images/chidon.png`} alt="Chidon" />,
      items: [
        { label: 'Shabbaton Enrolled Report', legacy: true, path: '/reports/chidon/shabbaton_enrollment.php' },
        { label: 'Shabbaton Walking Report', legacy: true, path: '/reports/chidon/walking_groups.php' },
        { label: 'Enter Chidon Test Marks', legacy: true, path: '/chidon_tests.php' },
        { label: 'Enroll Chaperones', legacy: true, path: '/chidon_school_reg.php' },
        { label: 'Activate Enrollment', legacy: true, path: '/enrollment.php' },
        { label: 'Review Enrollment', legacy: true, path: '/review_enrollment.php' },
        { label: 'Print Enrollment Info', legacy: true, path: '/chidon_review.php' },
      ]
    },
    {
      label: "Reports",
      icon: <img src={`${LEGACY_URL}/images/icon_report.png`} alt="Reports" />,
      items: [
        { label: 'Office Reports', legacy: true, path: '/reports.php', user_types: [ 'HQ' ] },
        { label: 'Registered Report', legacy: true, path: '/registered_report.php' },
        { label: 'Parents Report', legacy: true, path: '/parent_report.php' },
        { label: 'Not Yet Registered Report', legacy: true, path: '/non_registered_report.php' },
        { label: 'Barcodes Report', legacy: true, path: '/barcodes_report.php' },
        { label: 'Miles Report', legacy: true, path: '/miles.php' },
        { label: 'Auction Miles Report', legacy: true, path: '/auctionMiles.php' },
        { label: 'Missions Done Report', legacy: true, path: '/missions_report.php' },
        { label: "Stickers",
          items: [
            { label: 'Total Stickers Earned', legacy: true, path: '/stickers_report.php'  },
            { label: 'Total Stickers Earned By Date', legacy: true, path: '/stickers_report_by_week.php'  },
            { label: 'Total Stickers Earned By Child', legacy: true, path: '/stickers_report_by_child.php'  }
          ]
        },
        { label: "Birthdays",
          items: [
            { label: 'Birthday Report', legacy: true, path: '/names_report.php'  },
            { label: 'Birthdays By Date Range', legacy: true, path: '/find_birthdays_report.php'  }
          ]
        },
        { label: "Ranks / Medals",
          items: [
            { label: 'Rank Report', legacy: true, path: '/rank_report.php'  },
            { label: 'Mark Ranks / Medals as Received', legacy: true, path: '/admin_received_stats.php'  },
          ]
        }
      ]
    },
    {
      label: 'Shipping Reports',  legacy: true, path: '/reports/shipping',
      icon: <img src={`${LEGACY_URL}/images/icon_report.png`} alt="shipping-reports"/>
    },
    {
      label: "Campaigns",
      icon: <img src={`${LEGACY_URL}/images/parentIcons/Campaigns.gif`} alt="Campaigns" />,
      items: [
        { label: 'Tanya', 
          items: [
            { label: 'Individual Marking', legacy: true, path: '/editSoldierLines2.php' },
            { label: 'Yud Aleph Nissan Reports', legacy: true, path: '/yud_alef_nissan_choose.php' },
          ]
        },
        { label: 'Tehillim', 
          items: [
            { label: 'Mark Shabbos Mevorchim Tehillim', legacy: true, path: '/mark_tehillim2.php' },
            { label: 'Shabbos Mevorchim Report', legacy: true, path: '/choose_sm_report.php' },
            { label: 'Check Your Tehillim Quotas', legacy: true, path: '/tehillim_quotas.php' },
            { label: 'Change Tehillim Ladder/Quota', legacy: true, path: '/admin_users_track.php' },
            { label: 'Shabbos Mevorchim Tutorial Video', legacy: false, path: 'https://vimeo.com/195384916' },
          ]
        }
      ]
    },
    {
      label: "Rally",
      icon: <img src={`${LEGACY_URL}/images/parentIcons/Rally.gif`} alt="Rally" />,
      items: [
        { label: 'Promotion Picture Report', legacy: true, path: '/promotion_report.php' },
        { label: 'Teacher\'s Medal Ceremony Report', legacy: true, path: '/medal_rank_ceremony3.php' },
        { label: 'Raffle Winners', legacy: true, path: '/raffles/shared/forms/winners_form.php' }
      ]
    },
    {
      label: 'Raffles',  legacy: true, path: '/raffles/',
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Raffles"/>
    },
    {
      label: 'Yearly Prize',  legacy: true, path: '/yearly_prize/reports/',
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Yearly Prize"/>
    },
    {
      label: 'Setup Guide',  legacy: true, path: '/admin_setup_guide.php',
      icon: <img src={`${LEGACY_URL}/images/icon_wizard.png`} alt="Setup Guide"/>
    },
    {
      label: 'Mileage Program',
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Miliage Program"/>
    },
    {
      label: 'Support', legacy: true, path: '/helpdesk/?p=open',
      icon: <img src={`${LEGACY_URL}/images/parentIcons/support icon.gif`} alt="Support"/>
    },
    {
      label: 'Logout', path: '/logout',
      icon: <img src={`${LEGACY_URL}/images/parentIcons/logout.gif`} alt="Logout"/>
    },
  ];

  // filter the menu and return it
  return menu.reduce( menuReducer( user_type, DEFAULT_USER_TYPES ), [] );
} // end getMenu function

// export getMenu by default
export default getMenu;