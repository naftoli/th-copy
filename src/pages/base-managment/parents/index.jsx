import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import ParentsPage from './ParentsPage/ParentsPage';


class ParentsIndexPage extends Component {
  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ ParentsPage } />
        <Route path={`${path}/new`} render={() => <h1>New Parent</h1>}/>
        <Route path={`${path}/:id([0-9]+)`} render={() => <h1>View Parent</h1>}/>
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

// const mapStateToProps = ( state ) => ({
//   login: state.login.current_login
// })

export default ParentsIndexPage;
