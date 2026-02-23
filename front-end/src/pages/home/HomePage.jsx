import React, { useEffect } from 'react';
import { useSelector, useDispatch, shallowEqual } from 'react-redux';
import { Link } from 'react-router-dom';
import { Row } from 'reactstrap';

// components
import { BaseLogo } from 'components/ui';
import {
  QuickLinks, Resources, RegistrationWidget,
  BirthdaysWidget, PromotionsWidget
} from './widgets';

// state
import { getRegistration, getBirthdays, getPromotions } from 'store/home/operations';

// functions
import { setTitle } from 'functions/utils';
import { isBlank, mobileLogin } from 'functions/login';

// styles and images
import './home.scss';
import { man, woman } from 'img/th';
import school from 'img/icons/school.svg';
import parents from 'img/icons/parents.svg';
import RegistrationPage from 'pages/base-managment/base/RegisterBasePage';


const HomePage = () => {
  const dispatch = useDispatch();

  const { login, current_user, home } = useSelector(({ login, home }) => ({
    login: login.current_login,
    current_user: login.current_user,
    home,
  }), shallowEqual);

  const { name, img, code, active, key } = login;

  useEffect(() => {
    if (active) {
      setTitle('Home Page');
    }
  }, [active]);

  const openParentPortal = () => {
    mobileLogin(key, false);
  };

  if (active && isBlank(code)) {
    return (
      <div id='HomePage' className='select-account-type'>
        <h1>Select Account Type</h1>
        <hr />
        <div className='account-type-option'>
          <Link to={'/register'}>
            <img src={school} alt='school' />
            <h4>Base Commander</h4>
            <p>Create a New Base</p>
          </Link>
        </div>

        <div className='account-type-option' onClick={openParentPortal}>
          <img src={parents} alt='parent' />
          <h4>Parent Portal</h4>
          <p>Connect your children to your account</p>
        </div>

        <div id='legal'>
          Icons made by <a href="https://www.freepik.com/" title="Freepik">Freepik </a>
          from <a href="https://www.flaticon.com/" title="Flaticon">www.flaticon.com </a>
          is licensed by <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank" rel="noopener noreferrer">CC 3.0 BY</a>
        </div>
      </div>
    );
  }

  // show the registration page to accounts that are currently disabled.
  if (!active) {
    return <RegistrationPage />;
  }

  // return the proper homepage
  return (
    <div id='HomePage'>
      <div id='logo'>
        <img src={man} alt='man' className='general' />
        <BaseLogo src={img} />
        <img src={woman} alt='woman' className='general' />
      </div>

      <h1>{name}</h1>
      <hr />

      <Row id='widgets'>
        <QuickLinks />

        <RegistrationWidget
          login={login}
          data={home.registration}
          refresh={() => dispatch(getRegistration())}
        />

        <Resources />

        <BirthdaysWidget
          refresh={(fromDate, toDate) => dispatch(getBirthdays(fromDate, toDate))}
          birthdays={home.birthdays}
        />

        <PromotionsWidget
          refresh={(fromDate, toDate) => dispatch(getPromotions(fromDate, toDate))}
          promotions={home.promotions}
        />

      </Row>
    </div>
  );
};

export default HomePage;

