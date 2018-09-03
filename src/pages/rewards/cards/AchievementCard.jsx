import React from 'react';
// components
import Barcode from 'react-barcode';
import { Number, BaseLogo } from 'components/ui';

import achieivement_card from 'img/rewards/achieivement_card.png';

const AchievementCard = props => {
  return (
    <div className='AchievementCard'>
      <div className='card'>
        <div className='icon'>
          <img src={ achieivement_card } alt='achieivement_card' />
        </div>

        <div className='logos'>
          <BaseLogo src={ props.logo } alt='base' id='base' />
          <div className='card-details'>
            <p className='campaign'>{ props.campaign }</p>
            <p className='task'>{ props.task }</p>
            <p className='miles'>
              <Number value={ props.miles } /> Miles
            </p>
          </div>
          <BaseLogo src={ props.campaignLogo } alt='campaign' id='campaign' />
        </div>

        <div className='barcode'>
          <Barcode
            width={ 2 }
            margin={ 0 }
            height={ 25 }
            fontSize={ 10 }
            value={ `${ props.card_serial }` } />
        </div>
      </div>
    </div>
  );
}

export default AchievementCard;