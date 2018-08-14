import React from 'react';
import { LEGACY_URL } from 'components/constants';
import classnames from 'classnames';
import is from 'is_js';
import './styles/ProfilePicture.scss';

const fallbackImage = `${LEGACY_URL}/mobile/reg/images/profile-photo-default.jpg`;

const handleError = ( props ) => ( e ) => {
  e.target.src = fallbackImage ;
  if ( props.onError ) props.onError( e );
}

const ProfilePicture = ( props ) => {
  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' ) event.target.children[0].click();
  }

  const src = props.src ? `${LEGACY_URL}${props.src}` : '';

  return (
    <div tabIndex={ props.tabIndex || 0 } onKeyPress={onKeyPress}
        className={classnames( `profile-picture`, { editable: !!props.onClick, ie: is.ie() } )}>
      <img { ...props } src={ src } className={ classnames( props.className, 'profile-img' ) } 
        onError={ handleError( props ) } alt='profile' />
      { !!props.rank && 
        <img src={`${LEGACY_URL}/mobile/img_new/ranks/${props.rank}.svg`} 
          className='rank' alt='rank' />
      }
    </div>
  );
}

export default ProfilePicture;
