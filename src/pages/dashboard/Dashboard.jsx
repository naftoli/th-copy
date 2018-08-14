import React, { Component } from 'react';
import Sidebar, { getMenu } from 'components/navigation/Sidebar';
import Navbar from 'components/navigation/Navbar';
import { LEGACY_URL } from 'components/constants';
import { FontAwesome } from 'components/ui';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import { changeLogin } from 'store/login/actions';
import { ClientError } from 'pages/errors';
import './Dashboard.scss';

const threshold = 768;

export class Dashboard extends Component {
  // default props
  static defaultProps = {
    history: { listen: () => { return () => {} } }, // function that returns a function
    current_login: {},
    current_user: {}
  }
  // initial state
  state = { active: false, hasError: false }
  // setup initial state on component mount
  componentDidMount() {
    // open the sidebar by default on larger displays
    if ( window.innerWidth > threshold ) {
      this.setState({ active: true });
    }
    // close the sidebar if the route changes
    this.unlisten = this.props.history.listen( () => {
      const active = window.innerWidth <= threshold ? false : this.state.active;
      this.setState({ hasError: false, active })
    });
  }
  // when component unmounts unlisten to history
  componentWillUnmount() {
    this.unlisten();
  }
  // handle application wide errors in a gracefull way in production
  componentDidCatch( error, info ) {
    // Display fallback UI
    this.setState({ hasError: true });
    // You can also log the error to an error reporting service
    console.warn( error, info );
  }

  // only toggle the sidebar if the screen is small enough
  toggle = () => {
    if ( window.innerWidth <= 1024 ) {
      this.setState({ active: !this.state.active });
    }
  }

  render() {
    const { current_user, current_login, changeLogin, location } = this.props;
    // if we are a user and not logging out - redirect to legacy parent portal
    if ( current_login.type === 'user' && location.pathname !== '/logout' ) {
      window.location.href = `${LEGACY_URL}/mobile/reg/parent_detail.html`;
      return null;
    }

    const menu = getMenu( current_login.code, current_login.ckids );
    // add a logout button
    menu.push({
      label: 'Logout', path: '/logout',
      icon: <FontAwesome icon='sign-out-alt' />
    });

    return (
      <div id="dashboard">
        <Navbar onClick={ this.toggle } onLoginChange={ changeLogin }
          logins={ current_user.logins } current_login={current_login} />
        <div id="dashboard-body">
          <Sidebar menu={ menu } active={ this.state.active } />
          <div id="dashboard-content">
            { this.state.hasError && <ClientError/> }
            { !this.state.hasError && this.props.children }
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToProps = ( state ) => ({
  current_user: state.login.current_user,
  current_login: state.login.current_login
});

export default withRouter(
  connect( mapStateToProps, { changeLogin } )( Dashboard )
);