import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { GoogleButton, ChabadOrgButton } from 'components/buttons';
import { InputGroup, InputGroupAddon, Button, UncontrolledAlert } from 'reactstrap';
// state
import { login, getCurrentUser } from 'store/login/operations';
import { setErrors } from 'store/login/actions';
// styles and images
import logo from 'img/logos/th.svg';
import { user } from 'img/icons';
import { Row, Col } from 'reactstrap';

const noop = () => {};

// export for tests
export class Login extends Component {
  // default state
  state = {
    username: '',
    password: '',
    show_password: false, 
    show_form: false
  }
  // default props
  static defaultProps = {
    errors: [],
    loading: false,
    setErrors: noop,
    getCurrentUser: noop,
    login: () => { return Promise.reject() },
  }
  // update the state
  handleChange = ({ target }) => {
    this.setState({
      [target.name]: target.value
    });
  }
  // handle username/password login
  handleLoginForm = ( event ) => {
    event.preventDefault();
    this.setState( { show_password: false } ); // reset the show-password state
    // and log the user in
    this.login({
      username: this.state.username,
      password: this.state.password
    });
  }
  // handle chabad.org login
  onChabadOrgLogin = chabad_key =>
    this.login({ chabad_key })
  // handle google.com login
  onGoogleLogin = google_key =>
    this.login({ google_key })

  showLogin = () => {
    this.setState({ show_form: true });
  }

  // show errors
  login = opts => 
    this.props.login( opts )
    .then( this.props.getCurrentUser )
    .catch( e => this.props.setErrors(
      e.message || 'Could not log in. Please try again.'
    ));

  // * render the page.
  render(){
    let { errors, loading } = this.props;

    errors = errors.map( (error, index) => 
      <UncontrolledAlert color='danger' key={index}>{ error }</UncontrolledAlert> 
    );
    
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
                <a href="/signup">
                  <Button size="lg" color='primary' id='join'>
                      Create an Account
                  </Button>
                </a>
              </Col>
              <Col xs="6">
                <Button size="lg" color='primary' id='show_login' onClick={ this.showLogin }>
                  Log in to your account
                </Button>
              </Col>
            </Row>
        </div>
        <br />

        { loading && <Spinner size={ 8 } /> }

        { errors }

        { !loading && this.state.show_form && 
          <div className='form' id='login-form'>
            <form onSubmit={ this.handleLoginForm }>
              <InputGroup size="lg">

                <InputGroupAddon addonType="prepend">
                  <img src={ user } alt='username' />
                </InputGroupAddon>

                <input
                  name='username'
                  autoFocus required
                  placeholder='Username'
                  className='form-control'
                  value={ this.state.username }
                  onChange={ this.handleChange } />

              </InputGroup>

              <Password
                required
                showIcon  size="lg"
                value={ this.state.password } 
                onChange={ this.handleChange } />

              <Button size="lg" color='primary' id='login'>
                Login <FontAwesome icon='sign-in-alt '/>
              </Button>
            </form>

            <div id='sign-in-with'>

              <GoogleButton size="sm" shrink
                tokenOnly onSuccess={ this.onGoogleLogin } />

              <ChabadOrgButton size="sm" shrink
                onLogin={ this.onChabadOrgLogin }/>

            </div>

            {/* <div id='links' className='clearfix'>
              <Link to='/forgot'>Forgot Password?</Link>
              <Link to='/signup'>New Account</Link>
            </div> */}

            {/* <footer>
              <a { ...linkProps } href={`${ LEGACY_URL }/donate`}>
                Donate
              </a>
              <a { ...linkProps } href={`${ LEGACY_URL }/our_mission.php`}>
                Our Mission
              </a>
              <a { ...linkProps } href={`${ LEGACY_URL }/plan_of_action.php`}>
                Plan of Action
              </a>
              <a { ...linkProps } href={`${ LEGACY_URL }/campaigns.php`}>
                Campaigns
              </a>
            </footer> */}
          </div>
        }
      </div>
    );
  }
}

const mapStateToProps = ( state ) => ({
  loading: state.login.loading,
  errors: state.login.errors
});

const mapDispatchToProps = {
  login, setErrors, getCurrentUser
}

export default connect( mapStateToProps, mapDispatchToProps )( Login );