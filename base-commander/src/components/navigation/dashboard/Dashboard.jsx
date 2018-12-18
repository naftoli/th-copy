import React, { Component } from 'react';
import { connect } from 'react-redux';
import { withRouter } from 'react-router';
// other
import { LEGACY_URL } from 'components/constants';
import { Navbar, Sidebar, getMenu } from '../index';
import { changeLogin } from 'store/login/actions';
// pages
import { ClientError } from 'pages/errors';
// styles
import './Dashboard.scss';

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
    if ( window.innerWidth <= threshold && this.props.login.active ) {
      this.setState({ active: !this.state.active });
    }
  }

  render() {
    let {     children,   login,
      title,  location,   current_user,
    } = this.props;
    const { active, hasError } = this.state;

    let content = children;
    let sidebar = null;

    if ( hasError ) {
      content = <ClientError/>;
    }

    const menu = getMenu( login );

    // if we are a user and not logging out - redirect to legacy parent portal
    if ( location && location.pathname !== '/logout' ) {
      if ( login.type === 'PARENT' ) {
        window.location.href = `${LEGACY_URL}/mobile/reg/parent_detail.html`;
        return null;
      }
    }

    // if the login is active, show the sidebar
    if ( login.active ) {
      sidebar = <Sidebar menu={ menu } active={ active } />
    }

    return (
      <div id="dashboard">

        <Navbar 
          title={ title }
          currentLogin={ login }
          onClick={ this.toggle }
          showMenu={ login.active }
          logins={ current_user.logins }
          onLoginChange={ this.onLoginChange } />

        <div id="dashboard-body">

          { sidebar }

          <div id="dashboard-content">
            { content }
          </div>

        </div>
      </div>
    )
  }
}

const mapDispatchToProps = { changeLogin }

export default withRouter(
  connect( null, mapDispatchToProps )( Dashboard )
);
