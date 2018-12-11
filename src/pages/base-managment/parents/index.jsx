import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import ParentPage from './ParentPage';
import ParentsPage from './ParentsPage';
// styles
import './parents.scss';

class ParentsIndexPage extends Component {
  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ ParentsPage } />
        <Route path={`${path}/:id([0-9]+)`} component={ ParentPage } />
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

// const mapStateToProps = ( state ) => ({
//   login: state.login.current_login
// })

export default ParentsIndexPage;
