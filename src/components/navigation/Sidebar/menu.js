import React from 'react';
// url to use when navigating to older pages
export const LEGACY_URL = process.env.NODE_ENV === "production" ? "//mashpia.com" : "//test.mashpia.com";

// Default menu for Base Commanders
const menu = [
  {
    label: 'Home',
    legacy: true,
    icon: <img src={`${LEGACY_URL}/images/icon_admin_home.png`} alt="home"/>,
    path: '/admin.php'
  },
  {
    label: 'Base Managment',
    icon: <img src={`${LEGACY_URL}/images/icon_dashboard.png`} alt="base-managment"/>,
    children: [
      {
        label: "Soldiers",
        children: [
          {
            label: "View / Edit"
          },
          {
            label: "Registration"
          },
          {
            label: "Rank Cards"
          },
          {
            label: "Update Missions",
            legacy: true,
            path: '/add_missions.php'
          },
          {
            label: "Update Missions",
            legacy: true,
            path: '/add_medals.php'
          },
        ]
      },
      {
        label: "Plattons"
      },
      {
        label: "Parents"
      },
      {
        label: "Staff"
      },
      {
        label: "Base",
        children: [
          {
            label: 'View / Edit'
          },
          {
            label: 'Settings'
          },
          {
            label: 'Transactions'
          }
        ]
      }
    ]
  } // end Base Managment
]

export default menu;