import React, { Component } from 'react';
// components
import { BaseRow } from '../rows';
import { AddressRow } from 'components/rows';
import { Row, Col, Input } from 'reactstrap';
import { ProfilePicture } from 'components/ui';
// functions
import { eventToUpdate } from 'functions/events';

export class BaseTab extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  render(){
    const { base, onUpdate } = this.props;

    return (
      <div id='BaseTab'>
        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
            <h3>Base #{base.school_number}</h3>

            <BaseRow 
              { ...base }
              onUpdate={ onUpdate } />
              
          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <ProfilePicture src={ base.logoPaths.logo }/>
          </Col>
        </Row>

        <AddressRow { ...base } prefix='school_' onChange={ this.onChange } />

        <p className='title'>Notes</p>
        <Input type="textarea" name='notes' rows='8'
          value={ base.notes } onChange={ this.onChange } />

      </div>
    )
  }
}
