import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { Redirect } from 'react-router-dom';
// import { GoogleButton, ChabadOrgButton } from 'components/buttons';
import { InputGroup, InputGroupAddon, Button, UncontrolledAlert } from 'reactstrap';
// state
import { login, getCurrentUser } from 'store/login/operations';
import { setErrors } from 'store/login/actions';
// styles and images
import logo from 'img/logos/th.svg';
import { user } from 'img/icons';

const noop = () => {};

// export for tests
export class Login extends Component {
  // default state
  state = {
    username: '',
    password: '',
    show_password: false, 
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
    let { errors, loading, logged_in } = this.props;
    if (logged_in) {
      const redirectTo = new URLSearchParams(this.props.location.search).get("redirect_to");
      return <Redirect to={redirectTo || "/"}/>
    }

    errors = errors.map( (error, index) => 
      <UncontrolledAlert color='danger' key={index}>{ error }</UncontrolledAlert> 
    );

    const imgStyle = { 'maxWidth':' 200px' };
    const inputStyle = { 'margin': '12px 0' };
    
    return (
      <div id='Login'>
        <div>
          <img src={logo} id='logo' alt='logo' style={ imgStyle } />

            <h1>Welcome to Tzivos Hashem</h1><br />
            An army of Jewish children united to bring Moshiach now!
            
        </div>

        { loading && <Spinner size={ 8 } /> }

        { errors }

        { !loading && 
          <div className='form' id='login-form'>
            <form onSubmit={ this.handleLoginForm }>
              <InputGroup size="lg">

                <InputGroupAddon addonType="prepend">
                  <img src={ user } alt='username' />
                </InputGroupAddon>

                <input
                  style={ inputStyle }
                  name='username'
                  autoFocus required
                  placeholder='Username'
                  className='form-control'
                  value={ this.state.username }
                  onChange={ this.handleChange } />

              </InputGroup>

              <Password
                style={ inputStyle }
                required
                showIcon  size="lg"
                value={ this.state.password } 
                onChange={ this.handleChange } />

              <Button size="lg" color='primary' id='login' style={ inputStyle }>
                Login <FontAwesome icon='sign-in-alt '/>
              </Button>
            </form>

            {/* <div id='sign-in-with'>

              <GoogleButton size="sm" shrink
                tokenOnly onSuccess={ this.onGoogleLogin } />

              <ChabadOrgButton size="sm" shrink
                onLogin={ this.onChabadOrgLogin } />

            </div> */}

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

const mapStateToProps = ( state ) => {
  const { current_login, current_user, loading, errors } = state.login;
  return {
    loading, errors,
    logged_in: Object.keys(current_login).length > 0 && !!current_user,
  }
};

const mapDispatchToProps = {
  login, setErrors, getCurrentUser
}

export default connect( mapStateToProps, mapDispatchToProps )( Login );