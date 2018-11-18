import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { InputGroup, InputGroupAddon, Button } from 'reactstrap';
import { GoogleButton, ChabadOrgButton } from 'components/buttons';
// state
import { login } from 'store/login/operations';
// styles and images
import logo from 'img/logos/th.svg';
import { user } from 'img/icons';

// export for tests
export class Login extends Component {
  // default state
  state = {
    username: '',
    password: '',
    show_password: false
  }
  // default props
  static defaultProps = {
    loading: false,
    errors: [],
    login: () => {}
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
    this.props.login({
      username: this.state.username,
      password: this.state.password
    })
  }
  // handle chabad.org login
  onChabadOrgLogin = chabad_key =>
    this.props.login({ chabad_key });
  // handle google.com login
  onGoogleLogin = google_key =>
    this.props.login({ google_key });

  // * render the page.
  render(){
    let { errors, loading } = this.props;

    errors = errors.map( (error, index) => 
      <div className="alert alert-danger" key={index}>{error}</div> 
    );
    
    return (
      <div id='Login'>
        <img src={logo} id='logo' alt='logo' />

        { loading && <Spinner size={ 8 } /> }

        { !loading &&
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

              { errors }

              <Button size="lg" color='primary' id='login'>
                Login <FontAwesome icon='sign-in-alt '/>
              </Button>
            </form>

            <div id='links' className='clearfix'>
              <Link to='/forgot'>Forgot Username/Password?</Link>
              <Link to='/signup'>New Account</Link>
            </div>

            <div id='sign-in-with'>

              <ChabadOrgButton size="lg"
                onLogin={ this.onChabadOrgLogin }/>

              <GoogleButton size="lg" tokenOnly
                onSuccess={ this.onGoogleLogin } />

            </div>

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

export default connect(mapStateToProps, { login })(Login);