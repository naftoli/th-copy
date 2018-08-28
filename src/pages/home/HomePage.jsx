import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
// import { Link } from 'react-router-dom';
import { BaseLogo } from 'components/ui';
import { Row } from 'reactstrap';
import { 
  QuickLinks, Resources, RegistrationWidget,
  BirthdaysWidget
} from './widgets';
// state
import { 
  getRegistration, getBirthdays 
} from 'store/home/operations';
// functions
import { setTitle } from 'functions/utils';
// styles and images
import './home.scss';
import './widgets/style.scss';
import { man, woman } from 'img/th';

class HomePage extends Component {

  componentDidMount() {
    setTitle( 'Home Page' );
  }

  render() {
    const { login, home, getRegistration } = this.props;
    const { name, img } = this.props.login;

    return (
      <div id='HomePage'>
        <div id='logo'>
          <img src={ man } alt='man' className='general' />
          <BaseLogo src={ img } />
          <img src={ woman } alt='woman' className='general' />
        </div>
        <h1>{ name }</h1>
        <hr/>

        <Row id='widgets'>

          <QuickLinks />

          <RegistrationWidget
            login={ login }
            data={ home.registration } 
            refresh={ getRegistration } />

          <BirthdaysWidget
            login={ login }
            refresh={ this.props.getBirthdays }
            birthdays={ home.birthdays } />

          <Resources />

        </Row>
      </div>
    );
  }
}

const mapStateToProps = ({ login, home }) => ({
  login: login.current_login,
  home,
})

const mapDispatchToProps = {
  getRegistration, getBirthdays
}

export default connect( mapStateToProps, mapDispatchToProps )( HomePage );
