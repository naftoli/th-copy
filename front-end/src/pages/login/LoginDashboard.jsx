import React from 'react';
// components
import { Button } from 'reactstrap';
import { Link, useLocation, Navigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
// styles and images
import logo from 'img/logos/th.svg';
import { Row, Col } from 'reactstrap';

const LoginDashboard = () => {
  const location = useLocation();
  const { pathname, search, hash } = location;
  const redirectTo = pathname + search + hash;

  const { current_login, current_user } = useSelector(state => state.login || {});
  const logged_in = current_login && Object.keys(current_login).length > 0 && !!current_user;

  if (logged_in) {
    return <Navigate to="/" replace />;
  }

  return (
    <div id='Login'>
      <div>
        <img src={logo} id='logo' alt='logo' />

        <h1>Welcome to Tzivos Hashem</h1><br />
        An army of Jewish children united to bring Moshiach now!
        <br /><br /><br />

        <h4>Family Accounts</h4>
        <hr />
        <Row>
          <Col xs="6">
            <a href="/mobile/reg/parent_register.html">
              <Button size="lg" color='primary' id='parent_join'>
                Create an Account
              </Button>
            </a>
          </Col>
          <Col xs="6">
            <a href="/mobile">
              <Button size="lg" color='primary' id='parent_login'>
                Log in to your account
              </Button>
            </a>
          </Col>
        </Row>
        <br />
        <h4>School Accounts</h4>
        <hr />
        <Row>
          <Col xs="6">
            <Link to={'/signup'}>
              <Button size="lg" color='primary' id='join'>
                Create an Account
              </Button>
            </Link>
          </Col>
          <Col xs="6">
            <Link to={`/login?redirect_to=${encodeURIComponent(redirectTo)}`}>
              <Button size="lg" color='primary' id='show_login'>
                Log in to your account
              </Button>
            </Link>
          </Col>
        </Row>
      </div>
    </div>
  );
};

export default LoginDashboard;