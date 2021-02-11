import React from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import ModulesPage from './ModulesPage';

function ModuleIndexPage ({match}) {
  return (
    <Switch>
      <Route path={ match.path } exact component={ ModulesPage } />
      <Route component={ Page404 } />
    </Switch>
  )
}

export default ModuleIndexPage;
