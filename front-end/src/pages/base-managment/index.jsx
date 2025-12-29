import React from 'react';
import { connect } from 'react-redux';
import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import SoldiersPages from './soldiers';
import PlatoonPages from './platoons';
import ParentPages from './parents';
import StaffPages from './staff';
import ModulePages from './module';
import BasePages from './base';
// functions
import { isBC } from 'functions/login';

export const BaseManagmentIndexPage = ({ login }) => {
  const { code } = login;

  return (
    <Routes>
      <Route path="soldiers/*" element={<SoldiersPages />} />
      {/* render BC only routes */}
      {isBC(code) && (
        <>
          <Route path="platoons/*" element={<PlatoonPages />} />
          <Route path="parents/*" element={<ParentPages />} />
          <Route path="staff/*" element={<StaffPages />} />
          <Route path="modules/*" element={<ModulePages />} />
          <Route path="base/*" element={<BasePages />} />
        </>
      )}
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect(mapStateToProps)(BaseManagmentIndexPage);
