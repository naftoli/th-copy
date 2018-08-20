import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
import classnames from 'classnames';
import is from 'is_js';
import './styles/ProfilePicture.scss';

export class ProfilePicture extends Component {

  static defaultProps = {
    fallbackImage: `${LEGACY_URL}/mobile/reg/images/profile-photo-default.jpg`
  }
  
  onKeyPress = event => {
    if ( event.key === 'Enter' ) event.target.children[0].click();
  }

  handleError = e => {
    e.target.src = this.props.fallbackImage;
    if ( this.props.onError ) this.props.onError( e );
  }

  render() {
    let { src, tabIndex, onClick, className, rank, fallbackImage, ...props } = this.props;
    // classnames
    const classNames = classnames( `profile-picture`, { editable: !!onClick, ie: is.ie() } );
    const imageClassNames = classnames( className, 'profile-img' );
    // update props
    tabIndex = tabIndex || 0;
    src = src ? `${LEGACY_URL}${src}` : ''

    return (
      <div tabIndex={ tabIndex } onKeyPress={ this.onKeyPress } className={ classNames }>

        <img { ...props } 
          onClick={ onClick }
          src={ src } alt='profile' 
          className={ imageClassNames } 
          onError={ this.handleError } 
          />
        
        {/* Show the rank icon if we need to */}
        { !!rank && 
          <img 
            className='rank' alt='rank' 
            src={`${LEGACY_URL}/mobile/img_new/ranks/${rank}.svg`} />
        }
      </div>
    );
  }
}
