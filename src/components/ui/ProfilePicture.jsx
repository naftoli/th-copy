import React from 'react';

const handleError = ( props ) => ( e ) => {
  e.target.src='//mashpia.com/mobile/reg/images/profile-photo-default.jpg';
  if ( props.onError ) {
    props.onError( e );
  }
}

const ProfilePicture = ( props ) => (
  <img { ... props } onError={ handleError( props ) } alt='profile'
    className={ props.className ? `${props.className} profile-picture` : 'profile-picture'}
  />
)

export default ProfilePicture;