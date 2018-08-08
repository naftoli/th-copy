import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { ProfilePicture } from 'components/ui';
import './RankCard.scss';

class RankCard extends Component {
  
  static propTypes = {
    permanent: PropTypes.bool,
    user: PropTypes.shape({
      rank: PropTypes.string,
      rank_ord: PropTypes.number,
      user_serial: PropTypes.string, 
      first: PropTypes.string,
      last: PropTypes.string,
      school_name: PropTypes.string,
      school_logo: PropTypes.string,
      profilePicture: PropTypes.string,
      barcode: PropTypes.string,
      platoon: PropTypes.string
    })
  }

  static defaultProps = {
    permanent: true
  }

  render() {
    const { 
      rank, user_serial, first, last, first_he, last_he,
      profilePicture, barcode, rank_ord, platoon,
      school_logo, school_name, member_since, valid_utill
    } = this.props.user;
    // classnames for cards. See SCSS for what they do
    const classNames = classnames('print RankCard', { 'permanent': this.props.permanent });

    return (
      <div className={classNames}>
        <div className={`rank-card rank-${rank_ord}`}>
          <div className='top'>
            This card entitles the cardholder to TH privileges with the { rank } rank
          </div>
          <div className='sig'>
            <p>Authorized Signature</p>
            <h1 className='rank-row'>
              <span className='rank'>{rank} </span>{first_he || first} {last_he || last}
            </h1>
            <p>Serial #: <strong>{ user_serial }</strong></p>
          </div>
          <div className='info'>
            <div className='logo'>
              <img src={ LEGACY_URL + school_logo } alt='school_logo' />
            </div>
            <div className='base'>
              <p>BASE #614199</p>
              <p><strong>{school_name}</strong></p>
              <p>Brooklyn, NY</p>
              <p><strong>Platoon: { platoon || 'N/A' }</strong></p>
            </div>
            <ProfilePicture src={ LEGACY_URL + profilePicture } />
          </div>
          <div className='barcode'>
            <div className='code'>
              <img src={`${LEGACY_URL}/barcode.php/${barcode}`} alt='barcode'/>
              { barcode }
            </div>
            <div className='stats'>
              <p>Member since<br/>{member_since}</p>
              { valid_utill && <p>Valid until<br/>{valid_utill}</p> }
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default RankCard;