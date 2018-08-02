import React, { Component } from 'react';
import facepalm from 'img/facepalm.svg';
import './styles/ClientError.scss';

class ClientError extends Component {
  render() {
    return (
      <div id='ClientError' className='error'>
        <img src={facepalm} alt='faceplam'/>
        <h1>Well, this was not supposed to happen...</h1>
        <p>
          It appears that this section of the website is broken at the moment.<br/><br/>
          <strong>Clearing the cache may help.</strong>&nbsp;
          If not please contact <a href='mailto:bugs@tzivoshashem.org'>bugs@tzivoshashem.org</a>. and let them know which page you are on.<br/><br/>
          (Don't worry, we do plan on automating that last part as soon as we can ;-))
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

export default ClientError;
