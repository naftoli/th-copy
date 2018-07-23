import React from 'react';

const handleError = ( props ) => ( e ) => {
  e.target.src='//mashpia.com/mobile/reg/images/profile-photo-default.jpg';
  if ( props.onError ) {
    props.onError( e );
  }
}

const ProfilePicture = ( props ) => (
  <div className='profile-picture'>
    <img { ... props } onError={ handleError( props ) } alt='profile' />
  </div>
);

export default ProfilePicture;