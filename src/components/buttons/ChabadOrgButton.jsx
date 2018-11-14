import React, { Component } from 'react';
import { Button } from 'reactstrap';

import chabad from 'img/logos/chabad_4.png'

export class ChabadOrgButton extends Component {

  myChabadLoginWindow = null;

  onClick = () => {
    // window.onLoginComplete = this.onStatusChange;

    if ( this.myChabadLoginWindow && !this.myChabadLoginWindow.closed ) {
      this.myChabadLoginWindow.focus();
    } else {
      let params = `p=${ window.location.protocol.substr(0, window.location.protocol.length - 1 )}&d=${encodeURIComponent( window.location.host ) }`
      this.myChabadLoginWindow = window.open(
        `https://www.chabad.org/api/login/form?474DBD09-F59F-433D-A755-5A97594FC4E1&${ params }`,
        'MyChabadLoginWindow',
        'width=390,height=420,resizable=no,scrollbars=0,location=yes'
      );
    }
  }

  // handle when the chabad.org status changes
  onStatusChange = event => {
    debugger;
    // get the data from the response
    const { Status, Key } = event.response;
    // pass the login key to the parent
    if ( Status && Key )
      this.props.onLogin( Key );
  }
  // create a blank node for the API to hook on to
  render() {

    const { onLogin, ...props } = this.props;

    return (
      <Button
        { ...props }
        onClick={ this.onClick }
        className='ChabadOrgButton'>

        <span>Login with</span>
        <img 
          alt='chabad' 
          src={ chabad } />

      </Button>
    );
  }
}

