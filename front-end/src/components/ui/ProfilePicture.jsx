import React from 'react';
import PropTypes from 'prop-types';
import {
  LEGACY_URL, DEFAULT_LOGO, DEFAULT_PROFILE,
  DEFAULT_PRIZE
} from 'components/constants';
import classnames from 'classnames';
import is from 'is_js';
import './styles/ProfilePicture.scss';

export const ProfilePicture = ({
  src, tabIndex, onClick, className, rank, fallbackImage = DEFAULT_PROFILE, onError, ...props
}) => {

  const onKeyPress = event => {
    if (event.key === 'Enter') event.target.children[0].click();
  }

  const handleError = e => {
    e.target.src = `${LEGACY_URL}${fallbackImage}`;
    if (onError) onError(e);
  }

  // classnames
  const classNames = classnames(`profile-picture`, { editable: !!onClick, ie: is.ie() });
  const imageClassNames = classnames(className, 'profile-img');
  // update props
  const finalTabIndex = tabIndex || (onClick ? 0 : -1);
  const finalSrc = src ? `${LEGACY_URL}${src}` : '';

  const logoStyle = { display: 'inline-block' }

  return (
    <div tabIndex={finalTabIndex} onKeyPress={onKeyPress} className={classNames} style={logoStyle} >

      <img {...props}
        onClick={onClick}
        src={finalSrc} alt='profile'
        className={imageClassNames}
        onError={handleError}
      />

      {/* Show the rank icon if we need to */}
      {!!rank &&
        <img
          className='rank' alt='rank'
          src={`${LEGACY_URL}/mobile/img_new/ranks/${rank}.svg`} />
      }
    </div>
  );
}

ProfilePicture.propTypes = {
  // absolute source around legacy_url
  src: PropTypes.oneOfType([PropTypes.bool, PropTypes.string]),
  // rank_ord
  rank: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
  // misc props
  onClick: PropTypes.func,
  tabIndex: PropTypes.number,
  fallbackImage: PropTypes.string,
  onError: PropTypes.func
}

export const BaseLogo = props => (
  <ProfilePicture fallbackImage={DEFAULT_LOGO} {...props} />
)

export const StorePrize = props => (
  <ProfilePicture fallbackImage={DEFAULT_PRIZE} {...props} />
)
