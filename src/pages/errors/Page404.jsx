import React, { Component } from 'react';
import { Link } from 'react-router-dom';
import doubt from 'img/doubt.svg';

export class Page404 extends Component {
  render() {
    return (
      <div id='Page404' className='error'>
        <img src={doubt} alt='doubt'/>
        <h1>Hu, looks like there is nothing here...</h1>
        <p>
          It appears that you have landed on a page which does not exist.
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
