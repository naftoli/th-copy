import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import mechanic from 'img/mechanic.svg';

export class Construction extends Component {
  render() {
    return (
      <div id='Construction' className='error'>
        <img src={ mechanic } alt='mechanic'/>
        <h1>Under Construction</h1>
        <p>
          Sorry this feature is not ready yet. Please come back later!
        </p>
        <p>
          <Link to='/'>Take me home</Link>
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
