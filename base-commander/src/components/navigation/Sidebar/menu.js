import React from 'react';
import { FontAwesome } from 'components/ui';
// constants
import { LEGACY_URL } from 'components/constants';
const DEFAULT_USER_TYPES = [ 'HQ', 'INST', 'BC' ];
const ALL_USER_TYPES = [ 'HQ', 'INST', 'BC', 'TEACHER' ];

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
export const menuReducer = ( login, defaults = DEFAULT_USER_TYPES ) => ( filtered = [], item ) => {
  const { code, legacy, modules } = login;
  // reduce the items down a bit
  if ( item.items ) {
    item = Object.assign( {}, item, 
      { items: item.items.reduce(
        menuReducer( login, item.user_types || defaults ), 
        []
      )}
    );
  }

  if (
    ( !legacy && item.legacy ) // hide legacy links from new institutions
    || ( item.module && modules && !modules[ item.module ] ) // hide menu if login does not have access to this module
  ) return filtered;
  
  // if the item is enabled for that code, add it to the sidebar
  if ( item.user_types && item.user_types.indexOf( code ) > -1 ) {
    filtered.push( item );
  // if the default for this section contains this code, add it
  } else if ( !item.user_types && defaults.indexOf( code ) > -1 ) {
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
const getMenu = ( login ) => {
  const { id } = login;

  if ( Object.keys( login ).length === 0 )
    return [];
  // Define the shape of the menu
  const menu = [
    {
      label: 'Home', path: '/', user_types: [ ...ALL_USER_TYPES, 'BLANK' ],
      icon: <FontAwesome icon='home'/>
    },
    {
      label: 'Base Management',
      icon: <FontAwesome icon='school'/>,
      items: [
        {
          label: "Soldiers",
          items: [
            { label: "View / Edit", path: '/bm/soldiers' },
            { label: "Registration", path: '/bm/soldiers/registration', module: 'chayolei', user_types: [ 'BC' ] },
            { label: "Rank Cards", path: '/bm/soldiers/cards' }
          ]
        },
        { label: "Platoons", path: '/bm/platoons' },
        { label: "Parents", path: '/bm/parents' },
        { label: "Staff", path: '/bm/staff' },
        { label: "Bases", user_types: [ 'HQ', 'INST' ], path: `/bm/base` },
        { label: "Base", user_types: [ 'BC' ], path: `/bm/base/${id}` },
      ]
    },
    {
      label: 'Platoon Management', user_types: [ 'TEACHER' ], path: '/bm/soldiers',
      icon: <FontAwesome icon='chalkboard-teacher' />,
    },
    {
      label: "Missions", user_types: ALL_USER_TYPES, module: 'chayolei',
      icon: <FontAwesome icon='award' />,
      items: [
        { label: 'Print', path: '/missions/print' },
        { label: 'Mark', path: '/missions/mark' },
        { label: 'Personalize', path: '/missions/personalize' },
        { label: 'Tasks', path: '/missions/tasks', user_types: DEFAULT_USER_TYPES },
        
        { label: "Mission Checklist (old)", legacy: true, path: '/mission_sheets_checklist.php' },
        { label: "Mission Report (old)", legacy: true, path: '/missions_report.php' },
        // { label: 'Mission Report', path: '/missions/report' }
      ]
    },
    {
      label: "Rewards Program", user_types: ALL_USER_TYPES, module: 'rewards',
      icon: <FontAwesome icon='shopping-cart' />,
      items: [
        { label: "Achievement Cards", path: '/rewards/cards' },
        { label: "Tasks", path: '/rewards/tasks' },
        // { label: "Miles Grid", path: '/rewards/miles-grid' },
        { label: "Prizes", path: '/rewards/prizes' },
        { label: "Prize Templates", path: '/rewards/templates', user_types: [ 'HQ' ] },
        { label: "Orders", path: '/rewards/orders', user_types: [ 'BC', 'TEACHER' ] },
        { label: 'Add / Subtract Miles', path: '/rewards/miles', user_types: DEFAULT_USER_TYPES }
      ]
    },
    {
      label: "Chidon", legacy: true, module: 'chidon',
      icon: <img src={`${LEGACY_URL}/images/chidon.png`} alt="Chidon" />,
      items: [
        { label: 'Registered for Chidon', legacy: true, path: '/reports/chidon/chidon_enrollment.php' },
        { label: 'Shabbaton Enrolled Report', legacy: true, path: '/reports/chidon/shabbaton_enrollment.php' },
        { label: 'Shabbaton Walking Report', legacy: true, path: '/reports/chidon/walking_groups.php' },
        { label: 'Enter Chidon Test Marks', legacy: true, path: '/chidon_tests.php' },
        { label: 'Enroll Chaperones', legacy: true, path: '/chidon_school_reg.php' },
        
        { label: 'Activate Enrollment', legacy: true, user_types: ['BC'], path: '/enrollment.php' },
        { label: 'Activate Enrollment HQ', legacy: true, user_types: ['HQ'], path: '/enrollment_hq.php' },
        
        { label: 'Review Enrollment', legacy: true, path: '/review_enrollment.php' },
        { label: 'Print Enrollment Info', legacy: true, path: '/chidon_review.php' },

        { label: 'Generate ID Cards', legacy: true, user_types: ['HQ'], path: '/chidon/IDcards/' },
        { label: 'Upload Spreadsheets', legacy: true, user_types: ['HQ'], path: '/chidon/upload/' },
        { label: 'Chidon Office Reports', legacy: true, user_types: ['HQ'], path: '/reports/chidon/' },
      ]
    },
    {
      label: "Reports", legacy: true, module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/icon_report.png`} alt="Reports" />,
      items: [
        { label: 'Office Reports', legacy: true, path: '/reports/', user_types: [ 'HQ' ] },
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
      label: 'Shipping Reports',  legacy: true, path: '/reports/shipping', module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/icon_report.png`} alt="shipping-reports"/>
    },
    {
      label: "Campaigns", legacy: true,
      icon: <img src={`${LEGACY_URL}/images/parentIcons/Campaigns.gif`} alt="Campaigns" />,
      items: [
        { label: 'Tanya', module: 'tanya',
          items: [
            { label: 'Individual Marking', legacy: true, path: '/editSoldierLines2.php' },
            { label: 'Yud Aleph Nissan Reports', legacy: true, path: '/yud_alef_nissan_choose.php' },
          ]
        },
        { label: 'Tehillim', module: 'tehillim',
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
      label: "Rally", legacy: true, module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/parentIcons/Rally.gif`} alt="Rally" />,
      items: [
        { label: 'Promotion Picture Report', legacy: true, path: '/promotion_report.php' },
        { label: 'Teacher\'s Medal Ceremony Report', legacy: true, path: '/medal_rank_ceremony3.php' },
        { label: 'Raffle Winners', legacy: true, path: '/raffles/shared/forms/winners_form.php' }
      ]
    },
    {
      label: 'Raffles',  legacy: true, path: '/raffles/', module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Raffles"/>
    },
    {
      label: 'Yearly Prize',  legacy: true, path: '/yearly_prize/reports/', module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Yearly Prize"/>
    },
    {
      label: 'Setup Guide',  legacy: true, path: '/admin_setup_guide.php', module: 'chayolei',
      icon: <img src={`${LEGACY_URL}/images/icon_wizard.png`} alt="Setup Guide"/>
    },
  ];

  // filter the menu and return it
  return menu.reduce( menuReducer( login ), [] );
} // end getMenu function

// export getMenu by default
export default getMenu;