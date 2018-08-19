import React, { Component } from 'react';
// components
import { BaseRow } from '../rows';
import { Row, Col } from 'reactstrap';
import { ProfilePicture } from 'components/ui';

export class BaseTab extends Component {

  render(){
    const { base, onUpdate } = this.props;

    return (
      <div id='BaseTab'>
        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
            <h3>Base #{base.school_number}</h3>

            <BaseRow 
              { ...base }
              onUpdate={ onUpdate }
              />
          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <ProfilePicture src={ base.logoPaths.logo }/>
          </Col>
        </Row>

        {/* <AddressRow soldier={ soldier } onChange={ this.handleChange } /> */}

      </div>
    )
  }
}
