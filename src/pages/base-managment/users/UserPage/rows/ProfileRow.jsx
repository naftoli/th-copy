import React from 'react';
// components
import { Row, Col } from 'reactstrap';
import { ProfilePicture, FontAwesome } from 'components/ui';
import { Radio } from 'components/inputs';

const ProfileRow = ({ soldier, onChange, toggle }) => {

  const genderChange = ( event ) => {
    let { school_type_id } = soldier;
    const value = event.target.value;
    // format the data
    if ( ![2, 12, 22, 3, 13, 23].includes(school_type_id) ) {
      school_type_id = value === 'M' ? 2 : 3
    } else if ( value === 'M' && [3, 13, 23].includes(school_type_id) ) {
      school_type_id = school_type_id - 1;
    } else if ( value === 'F' && [2, 12, 22].includes(school_type_id) ) {
      school_type_id = school_type_id + 1;
    }
    // format the data
    onChange({
      [event.target.id]: event.target.value, school_type_id
    });
  }

  const { profilePicture, currentRank, gender  } = soldier;
  const rank = currentRank ? currentRank.rank : undefined;

  return (
    <Row>
      <Col xs='3' sm='12' style={{textAlign: 'center'}}>
        <ProfilePicture src={ profilePicture } className='inline-profile' 
          rank={ rank } onClick={ toggle } />
      </Col>
      <Col xs='9' sm='12'>
        <label>Gender</label>
        <div id='gender-row'>
          <Radio type='radio' name='gender' id='gender' value='M' 
              checked={ gender === 'M' } onChange={ genderChange }>
            Boy <FontAwesome icon='male' />
          </Radio>
          <Radio type='radio' name='gender' id='gender' value='F'
              checked={ gender === 'F' } onChange={ genderChange }>
            Girl <FontAwesome icon='female' />
          </Radio>
        </div>
      </Col>
    </Row>
  );
}

export default ProfileRow;
