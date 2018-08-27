import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { InputGroup, InputGroupAddon, Button } from 'reactstrap';
// import { GoogleButton, ChabadOrgButton } from 'components/buttons';
// state
import { login } from 'store/login/operations';
// styles and images
import './Login.scss';
import { logo } from 'img/logos';
import { user } from 'img/icons';
import { LEGACY_URL } from 'components/constants';

export class Login extends Component {
  constructor( props ){
    super( props );
    this.state = {
      username: '',
      password: '',
      show_password: false
    }
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
    this.props.login( this.state.username, this.state.password )
  }

  render(){
    let { errors, loading } = this.props;

    errors = errors.map( (error, index) => 
      <div className="alert alert-danger" key={index}>{error}</div> 
    );
    
    return (
      <div id='Login'>
        <div id='login-page'>
          <img src={logo} id='logo' alt='logo' />

          { loading && <Spinner size={ 8 } /> }

          { !loading &&
          <div id='login-form'>
            <form onSubmit={ this.handleLoginForm }>
              <InputGroup size="lg">
                <InputGroupAddon addonType="prepend">
                  <img className="pt-icon" src={user} alt='username' width='26' height='26'/>
                </InputGroupAddon>
                <input className='form-control' placeholder='Username' autoFocus='true' required
                  onChange={this.handleChange} value={this.state.username} name='username' />
              </InputGroup>

              <Password size="lg" value={this.state.password} showIcon onChange={this.handleChange} />

              { errors }

              <Button size="lg" color='primary' id='login'>
                Login <FontAwesome icon='sign-in-alt '/>
              </Button>
            </form>

            {/* <strong>Sign In With:</strong>
            <div id='sign-in-with'>
              <ChabadOrgButton size="lg" />
              <GoogleButton size='lg'/>
            </div> */}
          </div>
          }
        </div>
        <div id='links'>
          <a href={LEGACY_URL + '/mobile/reg/forgot.html'}> Forgot Password </a>|
          <a href={LEGACY_URL + '/registration.php'}> New Base </a>|
          <a href={LEGACY_URL + '/mobile/reg/parent_register.html'}> New Parent Account </a>
        </div>
      </div>
    );
  }
}

const mapStateToProps = ( state ) => ({
  loading: state.login.loading,
  errors: state.login.errors
});

export default connect(mapStateToProps, { login })(Login);