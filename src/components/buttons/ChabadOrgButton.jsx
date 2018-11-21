import React, { Component } from 'react';
import { Button } from 'reactstrap';

import classnames from 'classnames';
import chabad from 'img/logos/chabad_2.png'

export class ChabadOrgButton extends Component {

  myChabadLoginWindow = null;

  componentDidMount() {
    window.addEventListener('message', this.onKeyReceived );
  }

  componentWillUnmount() {
    window.removeEventListener('message', this.onKeyReceived );
  }

  onClick = () => {
    // if we have the window, put it in focus
    if ( this.myChabadLoginWindow && !this.myChabadLoginWindow.closed ) {
      this.myChabadLoginWindow.focus();
    // otherwise, open the url in a new tab
    } else {
      // let params = `p=${ window.location.protocol.substr(0, window.location.protocol.length - 1 )}&d=${encodeURIComponent( window.location.host ) }`
      this.myChabadLoginWindow = window.open(
        `https://www.pcgeekbrain.com/redirect.php`,
        'MyChabadLoginWindow',
        'width=390,height=420,resizable=no,scrollbars=0,location=yes'
      );
    }
  }

  // handle when the chabad.org status changes
  onKeyReceived = event => {
    // make sure that the event is from the window we opened up
    if ( event.source !== this.myChabadLoginWindow )
      return false;
    // make sure we have a key
    if ( !event.data.key )
      return false;
    
    this.myChabadLoginWindow.close();
    this.props.onLogin( event.data.key );
  }
  // create a blank node for the API to hook on to
  render() {
    let { onLogin, shrink, className, ...props } = this.props;

    className = classnames({
      'shrink': shrink,
      [className]: className,
      'ChabadOrgButton': true,
    });

    return (
      <Button
        { ...props }
        onClick={ this.onClick }
        className={ className } >

        <span>Login with&nbsp;</span>
        <img 
          alt='chabad' 
          src={ chabad } />

      </Button>
    );
  }
}

