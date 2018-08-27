import React, { Component } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL } from 'components/constants';
// components
// import { Link } from 'react-router-dom';
import { FontAwesome, BaseLogo } from 'components/ui';
// styles and images
import './home.scss';
import { man, woman } from 'img/th';

class HomePage extends Component {

  render() {

    const { name, img, ckids } = this.props.login;

    return (
      <div id='HomePage'>
        <div id='logo'>
          <img src={ man } alt='man' className='general' />
          <BaseLogo src={ img } />
          <img src={ woman } alt='woman' className='general' />
        </div>
        <h1>{ name }</h1>

        <h2>Quick Links</h2>
        <div id='links'>
          <a href={`${LEGACY_URL}/print_missions2.php`} target='_blank'>
            <FontAwesome icon='print' />
            Print Missions
          </a>
          <a href={`${LEGACY_URL}/mark_missions2.php`} target='_blank'>
            <FontAwesome icon='check-circle' regular />
            Mark Missions
          </a>
        </div>

        <div id='widgets'></div>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect( mapStateToProps )( HomePage );
