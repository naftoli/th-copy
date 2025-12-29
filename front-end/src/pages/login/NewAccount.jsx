import React, { useState } from 'react';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
// components
import Mosdos from './includes/Mosdos';
// import { Link } from 'react-router-dom';
// import { FontAwesome, Callout } from 'components/ui';
import { Callout } from 'components/ui';
import { AccountRow } from 'components/rows';
import { Label, Password } from 'components/inputs';
import { Row, Col, Collapse, UncontrolledAlert, Input } from 'reactstrap';
import { SaveButton } from 'components/buttons';
// import { GoogleButton, ChabadOrgButton, SaveButton } from 'components/buttons';
// styles and images
import logo from 'img/logos/th.svg';
// functions
import { onInputChange } from 'functions/events';
import { login as loginOp, getCurrentUser as getCurrentUserOp, createAccount as createAccountOp } from 'store/login/operations';

const NewAccount = () => {
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const [account, setAccount] = useState({});
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState([]);

  // handle the change
  const updateAccount = (updates) => {
    setAccount(prev => ({ ...prev, ...updates }));
  };

  // onchange event handler
  const onChange = onInputChange(updateAccount);

  // * attempt to login the user if they use a "login with" service
  const handleLogin = (opts) => {
    // if the login works redirect them to their account page to make edits
    return dispatch(loginOp(opts))
      .then(() => navigate('/myaccount', { replace: true }))
      .then(() => dispatch(getCurrentUserOp()));
  };

  // * handle google login information
  const onGoogleLogin = (google) => {
    // attempt to log them in
    handleLogin({ google_key: google.token_id })
      // and if it fails, autofill the form
      .catch(() => {
        const profile = google.getBasicProfile();

        updateAccount({
          last: profile.getFamilyName(),
          first: profile.getGivenName(),
          admin_email: profile.getEmail(),
          google_id: google.google_id
        });
      });
  };

  // * handle login with chabad.org
  const onChabadOrgLogin = (chabad_key) => {
    handleLogin({ chabad_key })
      // and if it fails, update the form
      .catch(response => {
        if (!response.data) return false;
        // extract variables we need to clean
        let { shliachID, phone, mosdos: mosdosList, ...shliach } = response.data;
        // set selected to fals on all the mosdos
        mosdosList = mosdosList && mosdosList.map(m => ({ ...m, selected: true }));
        // update the state
        updateAccount({
          ...shliach, mosdos: mosdosList,
          chabad_org_shliach_id: shliachID,
          admin_phone_work: phone
        });
      });
  };

  // turn a mosad on or off
  const selectMosad = (id) => {
    let { mosdos } = account;
    // return false if there are no mosdos
    if (!Array.isArray(mosdos))
      return false;
    // toggle the mosad to be checked/unchecked
    const newMosdos = mosdos.map(m => {
      if (m.id === id)
        return { ...m, selected: !m.selected };
      return m;
    });
    updateAccount({ mosdos: newMosdos });
  };

  // * create the new account
  const onSubmit = (e) => {
    e && e.preventDefault();
    setErrors([]);
    setSaving(true);
    // make the request to create an account and wait for the response
    dispatch(createAccountOp(account))
      .then(() => {
        navigate('/register', { replace: true });
      })
      .catch(e => {
        setSaving(false);
        setErrors(e.data || [e.message]);
      });
  };

  // check if username is unique
  const checkUsername = (e) => {
    if (e.target.value.length) {
      const url = import.meta.env.MODE === "production" ? "" : "//tzivos.local";
      fetch(url + '/ajax/checkDuplicateEmail.php?email=' + e.target.value)
        .then(result => {
          return result.json();
        })
        .then(data => {
          console.log(data);
          if (data === 1) {
            alert("This email address is already being used. Please choose a different one.");
            updateAccount({ admin_email: '' });
            e.target.focus();
            e.target.select();
          }
        })
        .catch(error => {
          console.log(error);
        });
    }
  };

  const errorAlerts = errors.map((e, i) =>
    <UncontrolledAlert color='danger' key={i}>{e}</UncontrolledAlert>
  );

  return (
    <div id='NewAccount'>
      <img src={logo} id='logo' alt='logo' />

      <h1>Tzivos Hashem</h1>

      <h3>New Base Commander Account</h3>
      <br />

      <form onSubmit={onSubmit} id='create-form'>

        <div id='account-login'>
          <p className='title'>Create a Login</p>
          <Row>
            {/* <Col xs={12} id='sign-in-with'>

              <GoogleButton
                disabled={ !!account.google_id }
                onSuccess={ onGoogleLogin } />

              <ChabadOrgButton
                onLogin={ onChabadOrgLogin }
                disabled={ !!account.chabad_org_shliach_id } />

            </Col> */}

            <Col xs={12}>
              <Label>E-mail Address (this will be your username)</Label>

              <Input
                required
                autoFocus
                type='email'
                name='admin_email'
                placeholder='Username'
                autoComplete='username'
                onChange={onChange}
                onBlur={checkUsername}
                value={account.admin_email || ''} />
            </Col>

            <Col sm={6}>
              <Label>Create a Password</Label>

              <Password required
                name='password'
                placeholder='Create a Password'
                onChange={onChange}
                autoComplete='new-password'
                value={account.password || ''} />
            </Col>

            <Col sm={6}>
              <Label>Confirm Password</Label>

              <Password required
                name='confirm'
                onChange={onChange}
                autoComplete='new-password'
                placeholder='Confirm Password'
                value={account.confirm || ''} />
            </Col>
          </Row>
        </div>

        <div id='account-information'>
          <p className='title'>Personal Information</p>

          <AccountRow
            account={account}
            onChange={onChange} />
        </div>

        <Collapse isOpen={!!account.mosdos} id='chabad-mosdos'>
          <p className='title'>Chabad.org Mosdos</p>

          <Callout color='info'>Please select the Mosdos you wish to use in Tzivos Hashem</Callout>

          <Mosdos
            mosdos={account.mosdos}
            onChange={selectMosad} />
        </Collapse>

        {errorAlerts}

        <Row>
          <Col xs={12} id='actions'>

            <SaveButton
              saving={saving}
              disabled={saving}>
              Create Account
            </SaveButton>

            {/* <Link className='btn btn-primary' to='/'>
              Login <FontAwesome icon='sign-in-alt' />
            </Link> */}
          </Col>
        </Row>
      </form>
    </div>
  );
};

export default NewAccount;
