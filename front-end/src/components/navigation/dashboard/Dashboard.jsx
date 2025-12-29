import React, { Component, useState, useEffect } from 'react';
import { connect } from 'react-redux';
import { useLocation } from 'react-router-dom';
// other
import { LEGACY_URL } from 'components/constants';
import { Navbar, Sidebar, getMenu } from '../index';
import { changeLogin } from 'store/login/actions';
// pages
import { ClientError } from 'pages/errors';
// styles
import './Dashboard.scss';

const threshold = 1025; // anything larger then an ipad

class DashboardErrorBoundary extends Component {
  state = { hasError: false }
  componentDidCatch(error, info) {
    this.setState({ hasError: true });
  }
  render() {
    if (this.state.hasError) return <ClientError />;
    return this.props.children;
  }
}

const DashboardContent = (props) => {
  const { children, login, title, current_user, changeLogin } = props;
  const location = useLocation();
  const [active, setActive] = useState(false);
  const [minimized, setMinimized] = useState(false);

  useEffect(() => {
    // close the sidebar if the route changes
    setActive(prev => window.innerWidth <= threshold ? false : prev);

    // Google Anylitics
    if (window.ga) {
      window.ga('set', 'page', location.pathname + location.search);
      window.ga('send', 'pageview', location.pathname + location.search);
    }
  }, [location]);

  const onLoginChange = (type, id) => {
    changeLogin(type, id);
  }

  // toggle sidebar: mobile (active) or desktop (minimized)
  const toggle = () => {
    if (window.innerWidth <= threshold) {
      if (login.active) setActive(!active);
    } else {
      setMinimized(!minimized);
    }
  }

  const menu = getMenu(login, current_user);

  // add a logout button
  menu.push({
    label: 'Logout', path: '/logout',
    icon: 'sign-out-alt'
  });

  // if we are a user and not logging out - redirect to legacy parent portal
  if (location.pathname !== '/logout') {
    if (login.type === 'PARENT') {
      window.location.href = `${LEGACY_URL}/mobile/reg/parent_detail.html`;
      return null;
    }
  }

  let sidebar = null;
  // if the login is active, show the sidebar
  if (login.active) {
    sidebar = <Sidebar menu={menu} active={active} minimized={minimized} />
  }

  return (
    <div id="dashboard">

      <Navbar
        title={title}
        currentLogin={login}
        onClick={toggle}
        showMenu={login.active}
        logins={current_user.logins}
        onLoginChange={onLoginChange} />

      <div id="dashboard-body">

        {sidebar}

        <div id="dashboard-content">
          {children}
        </div>

      </div>
    </div>
  )
}

const Dashboard = (props) => (
  <DashboardErrorBoundary>
    <DashboardContent {...props} />
  </DashboardErrorBoundary>
);

const mapDispatchToProps = { changeLogin }

export default connect(null, mapDispatchToProps)(Dashboard);
