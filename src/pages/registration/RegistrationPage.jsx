import React, { Component } from 'react';
import padlock from 'img/padlock.svg';

export class RegistrationPage extends Component {

  render() {
    return (
      <div id='RegistrationError' className='error'>
        <img src={ padlock } alt='faceplam'/>
        <h1>It appears that this base is not active.</h1>
        <p>
          Please speak to your base commander about activation.
        </p>
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