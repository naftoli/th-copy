import React, { useState } from 'react';
import { connect } from 'react-redux';
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { Navigate, useLocation } from 'react-router-dom';
import { InputGroup, InputGroupText, Button, UncontrolledAlert } from 'reactstrap';
import { login as loginOp, getCurrentUser as getCurrentUserOp } from 'store/login/operations';
import { setErrors as setErrorsOp } from 'store/login/actions';
import logo from 'img/logos/th.svg';
import { user } from 'img/icons';

const Login = (props) => {
  const { errors, loading, logged_in, login, getCurrentUser, setErrors } = props;
  const location = useLocation();

  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  // const [showPassword, setShowPassword] = useState(false); // Unused in original but present in state

  const handleChange = (e) => {
    const { name, value } = e.target;
    if (name === 'username') setUsername(value);
    if (name === 'password') setPassword(value);
  };

  const handleLoginForm = (event) => {
    event.preventDefault();
    // setShowPassword(false);
    login({ username, password })
      .then(getCurrentUser)
      .catch(e => setErrors(
        e.message || 'Could not log in. Please try again.'
      ));
  };

  if (logged_in) {
    const redirectTo = new URLSearchParams(location.search).get("redirect_to");
    return <Navigate to={redirectTo || "/"} replace />;
  }

  const errorAlerts = errors.map((error, index) =>
    <UncontrolledAlert color='danger' key={index}>{error}</UncontrolledAlert>
  );

  const imgStyle = { 'maxWidth': ' 200px' };
  const inputStyle = { 'margin': '12px 0' };

  return (
    <div id='Login'>
      <div>
        <img src={logo} id='logo' alt='logo' style={imgStyle} />

        <h1>Welcome to Tzivos Hashem</h1><br />
        An army of Jewish children united to bring Moshiach now!

      </div>

      {loading && <Spinner size={8} />}

      {errorAlerts}

      {!loading &&
        <div className='form' id='login-form'>
          <form onSubmit={handleLoginForm}>
            <InputGroup size="lg">

              <div className="input-group-prepend">
                <InputGroupText>
                  <img src={user} alt='username' />
                </InputGroupText>
              </div>

              <input
                style={inputStyle}
                name='username'
                autoFocus required
                placeholder='Username'
                className='form-control'
                value={username}
                onChange={handleChange} />

            </InputGroup>

            <Password
              style={inputStyle}
              required
              showIcon size="lg"
              value={password}
              name='password'
              onChange={handleChange} />

            <Button size="lg" color='primary' id='login' style={inputStyle}>
              Login <FontAwesome icon='sign-in-alt ' />
            </Button>
          </form>
        </div>
      }
    </div>
  );
};

const mapStateToProps = (state) => {
  const { current_login, current_user, loading, errors } = state.login;
  return {
    loading, errors,
    logged_in: Object.keys(current_login).length > 0 && !!current_user,
  };
};

const mapDispatchToProps = {
  login: loginOp, setErrors: setErrorsOp, getCurrentUser: getCurrentUserOp
};

export default connect(mapStateToProps, mapDispatchToProps)(Login);