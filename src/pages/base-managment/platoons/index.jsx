import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import PlatoonPage from './PlatoonPage';
import PlatoonsPage from './PlatoonsPage';
import PlatoonTransitionPage from './PlatoonTransitionPage/PlatoonTransitionPage';
// functions
import { connect } from 'react-redux';
// styles
import './platoons.scss';

export class PlatoonsIndexPage extends Component {

  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ PlatoonsPage } />
        <Route path={`${path}/:id([0-9]+)`} component={ PlatoonPage }/>
        <Route path={`${path}/transition`} component={ PlatoonTransitionPage }/>
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( PlatoonsIndexPage );
