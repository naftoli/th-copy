import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import PrizesPage from './PrizesPage';
import PrizePage from './PrizePage';
// functions
import { connect } from 'react-redux';
// styles
import './include/prizes.scss';

export class PrizesIndexPage extends Component {

  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ PrizesPage } />
        <Route path={`${path}/:id([0-9]+)`} component={ PrizePage }/>
        
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( PrizesIndexPage );
