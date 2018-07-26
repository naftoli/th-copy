import React from 'react';
import { LEGACY_URL } from 'components/constants';

const styles = { 
  profilePicture: { position: 'relative' },
  rankPicture: {
    position: 'absolute',
    right: '-8%', bottom: '-10%',
    height: '50%'
  }
}

const handleError = ( props ) => ( e ) => {
  e.target.src='//mashpia.com/mobile/reg/images/profile-photo-default.jpg';
  if ( props.onError ) {
    props.onError( e );
  }
}

const ProfilePicture = ( props ) => (
  <div className='profile-picture' style={styles.profilePicture}>
    <img { ...props } onError={ handleError( props ) } alt='profile' />
    { props.rank && 
      <img className='rank' src={`${LEGACY_URL}/mobile/img_new/ranks/${props.rank}.svg`} 
        style={styles.rankPicture} alt='rank' /> 
    }
  </div>
);

export default ProfilePicture;