import React from 'react';

// url to use when navigating to older pages
export const LEGACY_URL = process.env.NODE_ENV === "production" ? "//mashpia.com" : "//test.mashpia.com";

// generate the menu for now
const getMenu = () => {
  return [
    {
      label: 'Home',  legacy: true, path: '/admin.php',
      icon: <img src={`${LEGACY_URL}/images/icon_admin_home.png`} alt="home"/>
    },
    {
      label: 'Base Managment',
      icon: <img src={`${LEGACY_URL}/images/icon_dashboard.png`} alt="base-managment"/>,
      children: [
        {
          label: "Soldiers",
          children: [
            { label: "View / Edit" },
            { label: "Registration" },
            { label: "Rank Cards" },
            { label: "Update Missions", legacy: true, path: '/add_missions.php' },
            { label: "Update Medals", legacy: true, path: '/add_medals.php' },
          ]
        },
        { label: "Plattons" },
        { label: "Parents" },
        { label: "Staff" },
        {
          label: "Base",
          children: [
            { label: 'View / Edit' },
            { label: 'Settings' },
            { label: 'Transactions' }
          ]
        },
      ]
    },
    {
      label: "Missions",
      icon: <img src={`${LEGACY_URL}/images/icon_admin_medal.png`} alt="Missions" />,
      children: [
        { label: 'Print Missions', legacy: true, path: '/print_missions2.php' },
        { label: 'Print Summer Missions', legacy: true, path: '/print_missions_summer.php' },
        { label: 'Mark Missions', legacy: true, path: '/mark_missions2.php' },
        { label: 'Mark Yahadus', legacy: true, path: '/sefer_hamitzvos.php' },
        { label: 'Personalize Your Missions', legacy: true, path: '/task_customization.php' },
        { label: 'Add Tasks', legacy: true, path: '/newTask.php' },
        { label: 'Teachers Mission Checklist', legacy: true, path: '/mission_sheets_checklist.php' },
        { label: 'Missions Accomplished Report', legacy: true, path: '/missions_report.php' }
      ]
    },
    {
      label: "Achievement Cards",
      icon: <img src={`${LEGACY_URL}/images/icon_auction.png`} alt="Achievement Cards" />,
      children: [
        { label: 'Add Achievement Task', legacy: true, path: '/newAchievementTasks.php' },
        { label: 'Add / Subtract Points', legacy: true, path: '/manual_points.php' },
      ]
    },
    {
      label: "Chidon",
      icon: <img src={`${LEGACY_URL}/images/chidon.png`} alt="Chidon" />,
      children: [
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
      children: [
        { label: 'Add Achievement Task', legacy: true, path: '/newAchievementTasks.php' },
        { label: 'Add / Subtract Points', legacy: true, path: '/manual_points.php' },
      ]
    },
    {
      label: 'Shipping Reports',  legacy: true, path: '/reports/shipping',
      icon: <img src={`${LEGACY_URL}/images/icon_report.png`} alt="shipping-reports"/>
    },
  ]
} // end getMenu function

export default getMenu;