import React, { Component } from 'react';
import { Switch, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import PlatoonPage from './PlatoonPage/PlatoonPage';
import PlatoonsPage from './PlatoonsPage/PlatoonsPage';
import NewPlatoonPage from './PlatoonPage/NewPlatoonPage';
// functions
import { connect } from 'react-redux';


export class PlatoonsIndexPage extends Component {

  render() {
    const { path } = this.props.match;
    return (
      <Switch>
        <Route path={ path } exact component={ PlatoonsPage } />
        <Route path={`${path}/new`} component={ NewPlatoonPage }/>
        <Route path={`${path}/:id([0-9]+)`} component={ PlatoonPage }/>
        <Route component={ Page404 } />
      </Switch>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
})

export default connect( mapStateToProps )( PlatoonsIndexPage );
