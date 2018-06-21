import React from 'react';

const ProfilePicture = ( props ) => (
  <img 
    { ... props } 
    onError={ (e) => e.target.src='//mashpia.com/mobile/reg/images/profile-photo-default.jpg' } 
    className={ props.className ? `${props.className} profile-picture` : 'profile-picture'}
    />
)

export default ProfilePicture;