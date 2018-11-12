import React, { Component } from 'react';
import { Navbar, Sidebar, getMenu } from '../index';
import { LEGACY_URL } from 'components/constants';
import { FontAwesome } from 'components/ui';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import { changeLogin, resetState } from 'store/login/actions';
import { ClientError } from 'pages/errors';
import './Dashboard.scss';
import { RegistrationPage } from 'pages/registration/RegistrationPage';

const threshold = 1025; // anything larger then an ipad

export class Dashboard extends Component {
  // default props
  static defaultProps = {
    history: { listen: () => { return () => {} } }, // function that returns a function
    login: {},
    current_user: {}
  }
  // initial state
  state = { active: false, hasError: false }

  
  componentDidMount() {
    this.unlisten = this.props.history.listen( ( location ) => {

      // close the sidebar if the route changes
      const active = window.innerWidth <= threshold ? false : this.state.active;
      this.setState({ hasError: false, active });

      // Google Anylitics
      if ( window.ga && location ) {
        window.ga('set', 'page', location.pathname + location.search);
        window.ga('send', 'pageview', location.pathname + location.search);
      }
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
    // console.warn( error, info );
  }

  onLoginChange = ( type, id ) => {
    this.props.changeLogin( type, id );
  }

  // only toggle the sidebar if the screen is small enough
  toggle = () => {
    if ( window.innerWidth <= threshold ) {
      this.setState({ active: !this.state.active });
    }
  }

  render() {
    let {     children,   login,
      title,  location,   current_user,
    } = this.props;

    // if we are a user and not logging out - redirect to legacy parent portal
    if ( location && location.pathname !== '/logout' ) {

      if ( login.type === 'PARENT' ) {
        window.location.href = `${LEGACY_URL}/mobile/reg/parent_detail.html`;
        return null;
      }

      if ( !login.active ) {
        if ( login.code === 'BC' ) {
          const { legacy, id } = login;
          const { admin_id } = current_user;
          window.location.href = `${LEGACY_URL}/registration${!legacy ? '_ckids' : ''}.php?school_id=${id}&admin_id=${admin_id}`;
          return null;
        } else {
          children = <RegistrationPage />;
        }
      }
    }
    
    const menu = getMenu( login );
    // add a logout button
    menu.push({
      label: 'Logout', path: '/logout',
      icon: <FontAwesome icon='sign-out-alt' />
    });

    return (
      <div id="dashboard">

        <Navbar 
          title={ title } 
          currentLogin={ login }
          onClick={ this.toggle }
          logins={ current_user.logins }
          onLoginChange={ this.onLoginChange } />

        <div id="dashboard-body">

          <Sidebar menu={ menu }
            active={ this.state.active } />

          <div id="dashboard-content">
            { this.state.hasError && <ClientError/> }
            { !this.state.hasError && children }
          </div>

        </div>
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  current_user: login.current_user,
  login: login.current_login,
  title: login.title
});

const mapDispatchToProps = {
  changeLogin,  resetState
}

export default withRouter(
  connect( mapStateToProps, mapDispatchToProps )( Dashboard )
);
