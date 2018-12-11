import React from 'react';
// components
import { Row, Col } from 'reactstrap';
import { Radio } from 'components/inputs';
import { ProfilePicture, FontAwesome } from 'components/ui';

export const ProfileRow = props => {
  const { 
    src, rank, gender, 
    onGenderChange, onImageClick 
  } = props;

  const radioProps = {
    name: 'gender', id: 'gender',
    onChange: onGenderChange
  }

  return (
    <Row className='ProfileRow'>
      <Col xs='12'>
        <ProfilePicture 
          src={ src } 
          rank={ rank || 1 } 
          onClick={ onImageClick } />
      </Col>

      <Col xs='12' id='gender-row'>
          <Radio
            value='M'
            { ...radioProps }
            checked={ gender === 'M' }>
            Boy <FontAwesome icon='male' />
          </Radio>

          <Radio
            value='F'
            { ...radioProps }
            checked={ gender === 'F' }>
            Girl <FontAwesome icon='female' />
          </Radio>
      </Col>
    </Row>
  );
}
