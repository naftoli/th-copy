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

export class Login extends Component {

  state = {
    username: '',
    password: '',
    show_password: false
  }

  static defaultProps = {
    loading: false,
    errors: [],
    login: () => {}
  }

  handleChange = ({ target }) => {
    this.setState({
      [target.name]: target.value
    });
  }

  togglePassword = () => {
    // update the state
    this.setState({
      show_password: !this.state.show_password
    }, () => {
      // update the feild without re-rendering the component ( If we have access to the dom )
      const password = document.querySelector('#login-form #password');
      if ( password ) {
        password.type = this.state.show_password ? 'text' : 'password';
        password.focus();
      }
    });
  }

  handleLoginForm = ( event ) => {
    event.preventDefault();
    this.setState( { show_password: false } ); // reset the show-password state
    // and log the user in
    this.props.login({
      username: this.state.username,
      password: this.state.password
    })
  }

  onChabadOrgLogin = chabad_key => {
    this.props.login({ chabad_key })
  }

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

              <ChabadOrgButton 
                size="lg" 
                onLogin={ this.onChabadOrgLogin }/>

              {/* <GoogleButton
                size="lg" /> */}

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