import React, { useEffect } from 'react';
import { Button } from 'reactstrap';

import classnames from 'classnames';
import chabad from 'img/logos/chabad_2.png'

export const ChabadOrgButton = ({ onLogin, shrink, className, ...props }) => {

  const myChabadLoginWindow = React.useRef(null);

  // handle when the chabad.org status changes
  const onKeyReceived = event => {
    // make sure that the event is from the window we opened up
    if (event.source !== myChabadLoginWindow.current)
      return false;
    // make sure we have a key
    if (!event.data.key)
      return false;

    myChabadLoginWindow.current.close();
    onLogin(event.data.key);
  }

  useEffect(() => {
    window.addEventListener('message', onKeyReceived);
    return () => {
      window.removeEventListener('message', onKeyReceived);
    }
  }, []);

  const onClick = () => {
    // if we have the window, put it in focus
    if (myChabadLoginWindow.current && !myChabadLoginWindow.current.closed) {
      myChabadLoginWindow.current.focus();
      // otherwise, open the url in a new tab
    } else {
      // let params = `p=${ window.location.protocol.substr(0, window.location.protocol.length - 1 )}&d=${encodeURIComponent( window.location.host ) }`
      myChabadLoginWindow.current = window.open(
        `https://www.chabad.org/api/login/form/sso?474DBD09-F59F-433D-A755-5A97594FC4E1`,
        'MyChabadLoginWindow',
        'width=410,height=669,resizable=no,scrollbars=0,location=yes'
      );
    }
  }

  const buttonClassName = classnames({
    'shrink': shrink,
    [className]: className,
    'ChabadOrgButton': true,
  });

  return (
    <Button
      {...props}
      onClick={onClick}
      className={buttonClassName} >

      <span>Login with&nbsp;</span>
      <img
        alt='chabad'
        src={chabad} />

    </Button>
  );
}

