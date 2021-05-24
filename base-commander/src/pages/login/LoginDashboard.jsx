import React, { Component } from 'react';
// components
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
// styles and images
import logo from 'img/logos/th.svg';
import { Row, Col } from 'reactstrap';

// export for tests
export default class LoginDashboard extends Component {
  // * render the page.
  render() {
    const { pathname, search, hash } = this.props.location;
    const redirectTo = pathname+search+hash;
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
  }
}