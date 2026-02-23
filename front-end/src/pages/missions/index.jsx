import React from 'react';
import { connect } from 'react-redux';
import { Routes, Route } from 'react-router-dom';
// sub pages
import PersonalizePage from './personalize/PersonalizePage';
import { Page404, Construction } from 'pages/errors';
import PrintPage from './print/PrintPage';
import TasksPage from './tasks/TasksPage';
import MarkPage from './mark';
import TasksAccomplished from './streaks/TasksAccomplished';
import Streaks from './streaks/Streaks';
import DuchPage from './duch/DuchPage';
// functions
import { isBC } from 'functions/login';

export const MissionsIndexPage = ({ login }) => {
  const { code } = login;

  return (
    <Routes>
      <Route path="print" element={<PrintPage />} />
      <Route path="mark" element={<MarkPage />} />
      <Route path="personalize" element={<PersonalizePage />} />
      <Route path="tasks-accomplished" element={<TasksAccomplished />} />
      <Route path="streaks" element={<Streaks />} />
      <Route path="duch" element={<DuchPage />} />
      <Route path="report" element={<Construction />} />

      {isBC(code) &&
        <Route path="tasks" element={<TasksPage />} />}

      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect(mapStateToProps)(MissionsIndexPage);
