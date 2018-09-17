import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import TasksPage from './tasks/TasksPage';
import CardsPage from './cards/CardsPage';
import MilesPage from './miles/MilesPage';
import OrdersPage from './orders/OrdersPage';
import PrizesPage from './prizes/PrizesPage';
import TemplatesPage from './prizes/TemplatesPage';

export class BaseManagmentIndexPage extends Component {

  render() {
    const { path } = this.props.match;

    return (
      <Switch>
        <Route path={`${path}/cards`} component={ CardsPage } />
        <Route path={`${path}/tasks`} component={ TasksPage } />
        
        <Route path={`${path}/orders`} component={ OrdersPage } />

        <Route path={`${path}/prizes`} component={ PrizesPage } />
        <Route path={`${path}/templates`} component={ TemplatesPage } />

        <Route path={`${path}/miles`} component={ MilesPage } />

        <Route component={ Page404 } />
      </Switch>
    )
  }
}

export default BaseManagmentIndexPage;
// export link to legacy system
export { default as V2 } from './v2';
