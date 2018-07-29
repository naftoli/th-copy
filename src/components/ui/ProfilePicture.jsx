import React from 'react';
import { LEGACY_URL } from 'components/constants';
import classnames from 'classnames';
import './styles/ProfilePicture.scss'

const handleError = ( props ) => ( e ) => {
  e.target.src='//mashpia.com/mobile/reg/images/profile-photo-default.jpg';
  if ( props.onError ) props.onError( e );
}

const ProfilePicture = ( props ) => (
  <div className={classnames( `profile-picture`, { editable: !!props.onClick } )}>
    <img { ...props } className={ classnames( props.className, 'profile-img' ) } 
      onError={ handleError( props ) } alt='profile' />
    { props.rank && 
      <img src={`${LEGACY_URL}/mobile/img_new/ranks/${props.rank}.svg`} 
        className='rank' alt='rank' />
    }
  </div>
);

export default ProfilePicture;
