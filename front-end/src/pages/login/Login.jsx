import React, { useState } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import { InputGroup, InputGroupText, Button, UncontrolledAlert } from 'reactstrap';

// components
import { Password } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';
import { GoogleButton, ChabadOrgButton } from 'components/buttons';
import { LEGACY_URL } from 'components/constants';

// state
import { login, getCurrentUser } from 'store/login/operations';
import { setErrors } from 'store/login/actions';

// styles and images
import logo from 'img/logos/th.svg';
import { user } from 'img/icons';

const noop = () => { };

export const Login = () => {
  const dispatch = useDispatch();

  const { loading, errors } = useSelector(state => ({
    loading: state.login.loading || false,
    errors: state.login.errors || []
  }));

  const [formData, setFormData] = useState({
    username: '',
    password: '',
    show_password: false
  });

  const handleChange = ({ target }) => {
    setFormData(prev => ({
      ...prev,
      [target.name]: target.value
    }));
  };

  const handleLoginForm = (event) => {
    event.preventDefault();
    setFormData(prev => ({ ...prev, show_password: false }));
    performLogin({
      username: formData.username,
      password: formData.password
    });
  };

  const performLogin = (opts) => {
    return dispatch(login(opts))
      .then(() => dispatch(getCurrentUser()))
      .catch((e) => {
        dispatch(setErrors(e.message || 'Could not log in. Please try again.'));
      });
  };

  const onChabadOrgLogin = (chabad_key) => performLogin({ chabad_key });
  const onGoogleLogin = (google_key) => performLogin({ google_key });

  const linkProps = {
    target: '_blank', rel: 'noopener noreferrer'
  };

  return (
    <div id='Login'>
      <img src={logo} id='logo' alt='logo' />

      {loading && <Spinner size={8} />}

      {!loading && (
        <div className='form' id='login-form'>
          <form onSubmit={handleLoginForm}>
            <InputGroup size="lg">
              <div className="input-group-prepend">
                <InputGroupText>
                  <img src={user} alt='username' />
                </InputGroupText>
              </div>

              <input
                name='username'
                autoFocus required
                placeholder='Username'
                className='form-control'
                value={formData.username}
                onChange={handleChange}
              />
            </InputGroup>

            <Password
              required
              showIcon size="lg"
              value={formData.password}
              onChange={handleChange}
            />

            <Button size="lg" color='primary' id='login'>
              Login <FontAwesome icon='sign-in-alt ' />
            </Button>
          </form>

          <div id='sign-in-with'>
            <GoogleButton size="lg" shrink tokenOnly onSuccess={onGoogleLogin} />
            <ChabadOrgButton size="lg" shrink onLogin={onChabadOrgLogin} />
          </div>

          {errors.map((error, index) => (
            <UncontrolledAlert color='danger' key={index}>{error}</UncontrolledAlert>
          ))}

          <div id='links' className='clearfix'>
            <Link to='/forgot'>Forgot Password?</Link>
            <Link to='/signup'>New Account</Link>
          </div>

          <footer>
            <a {...linkProps} href={`${LEGACY_URL}/donate`}>Donate</a>
            <a {...linkProps} href={`${LEGACY_URL}/our_mission.php`}>Our Mission</a>
            <a {...linkProps} href={`${LEGACY_URL}/plan_of_action.php`}>Plan of Action</a>
            <a {...linkProps} href={`${LEGACY_URL}/campaigns.php`}>Campaigns</a>
          </footer>
        </div>
      )}
    </div>
  );
};

export default Login;