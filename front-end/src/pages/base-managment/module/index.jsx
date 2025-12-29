import React from 'react';
import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import ModulesPage from './ModulesPage';

function ModuleIndexPage() {
  return (
    <Routes>
      <Route index element={<ModulesPage />} />
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

export default ModuleIndexPage;
