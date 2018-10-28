import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import SoldierPage from './SoldierPage/SoldierPage';
import SoldiersPage from './SoldiersPage/SoldiersPage';
import RankCardsPage from './RankCardsPage/RankCardsPage';
import RegistrationPage from './RegistrationPage/RegistrationPage';
// functions
import { isBC } from 'functions/login';

export class SoldiersIndexPage extends Component {

  render() {
    const { code } = this.props.login;
    const { path } = this.props.match;
    const onlyBC = code === 'BC';
    return (
      <Switch>
        <Route path={ path } exact component={ SoldiersPage } />

        { onlyBC && <Route path={`${path}/registration`} component={ RegistrationPage }/> }
        { isBC( code ) && <Route path={`${path}/cards`} component={ RankCardsPage }/> }

        <Route path={`${path}/:id([0-9]+)`} component={ SoldierPage }/>

        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( SoldiersIndexPage );
