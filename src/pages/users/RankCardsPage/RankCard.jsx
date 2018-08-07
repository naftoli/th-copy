import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
import PropTypes from 'prop-types';
import classnames from 'classnames';
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
      school_logo: PropTypes.string,
      profilePicture: PropTypes.string,
      barcode: PropTypes.number,
      platoon: PropTypes.string
    })
  }

  static defaultProps = {
    permanent: true
  }

  render() {
    const { 
      rank, user_serial, first, last, school_logo, 
      profilePicture, barcode, rank_ord, platoon
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
              <span className='rank'>{rank} </span>{first} {last}
            </h1>
            <p>Serial #: <strong>{ user_serial }</strong></p>
          </div>
          <div className='info'>
            <div className='logo'>
              <img src={ LEGACY_URL + school_logo } alt='school_logo' />
            </div>
            <div className='base'>
              <p>BASE #614199</p>
              <p><strong>Avrohom Academy</strong></p>
              <p>Brooklyn, NY</p>
              <p><strong>Platoon: { platoon }</strong></p>
              </div>
            <div className='photo'>
              <img src={ LEGACY_URL + profilePicture } alt='profile'/>
            </div>
          </div>
          <div className='barcode'>
            <div className='code'>
              <img src={`${LEGACY_URL}/barcode.php/${barcode}`} alt='barcode'/>
              { barcode }
            </div>
            <div className='stats'>
              <p>Member since<br/> כ"ד אלול התשע"ה</p>
              <p>Valid until<br/> כ"ט אלול התשפ"ג</p>
            </div>
          </div>
        </div>
      </div>
    )
  }
}

export default RankCard;