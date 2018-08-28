import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
// import { Link } from 'react-router-dom';
import { BaseLogo } from 'components/ui';
import { Row } from 'reactstrap';
import { 
  QuickLinks, Resources, RegistrationWidget
} from './widgets';

// styles and images
import './home.scss';
import './widgets/style.scss';
import { man, woman } from 'img/th';

class HomePage extends Component {

  render() {

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

          <Resources />

          <RegistrationWidget />

        </Row>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( HomePage );
