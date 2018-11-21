import React, { Component } from 'react';
import { connect } from 'react-redux';
import padlock from 'img/padlock.svg';
import { isBC } from 'functions/login';
import { LEGACY_URL } from 'components/constants';

class RegistrationPage extends Component {

  render() {
    const { code, id } = this.props.login;

    let message = <p>Please speak to your base commander about activation.</p>;

    if ( isBC( code, true ) ) {
      const link = `${ LEGACY_URL }/registration.php?school_id=${id}`;
      message = <p>Click <a href={ link }>here</a> to begin registration</p>
    }

    return (
      <div id='RegistrationError' className='error'>
        <img src={ padlock } alt='faceplam'/>
        <h1>It appears that this base is not active.</h1>
        { message }
        <div id='legal'>
          Icon made by 
          <a href="https://www.flaticon.com/authors/twitter" target='_blank' rel="noopener noreferrer"> Twitter </a>
          from flaticon.com is licensed by 
          <a href="http://creativecommons.org/licenses/by/3.0/" title="Creative Commons BY 3.0" target="_blank" rel="noopener noreferrer"> CC 3.0 BY </a>
        </div>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
}

export default connect( mapStateToProps )( RegistrationPage )