import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { BaseRow } from '../rows';
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { BaseLogo } from 'components/ui';
import { SaveButton } from 'components/buttons';
import { Row, Col, Input, TabPane } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
// functions
import { onInputChange } from 'functions/events';

export class BaseTab extends Component {

  state = {
    showModal: false
  }

  onChange = onInputChange( this.props.onUpdate );

  toggle = () => this.setState({
    showModal: !this.state.showModal
  });
  
  updateLogo = formData => {
    this.toggle();
    this.props.updateBase( formData );
  }

  render(){
    const { base, onUpdate, tabId, updated, onSubmit, onValidChange } = this.props;

    return (
      <TabPane tabId={ tabId }>
        <Form id='BaseTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>
          <Row id='image-row'>
            <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
              <h3>Base #{base.school_number}</h3>

              <BaseRow 
                { ...base }
                onUpdate={ onUpdate } />
                
            </Col>
            <Col id='logo' xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
              <BaseLogo
                onClick={ this.toggle } 
                src={ base.logoPaths.logo } />
            </Col>
          </Row>

          <AddressRow { ...base } showPhone prefix='school_' onChange={ this.onChange } />

          <p className='title'>Notes</p>
          <Input type="textarea" name='notes' rows='6'
            value={ base.notes || '' } onChange={ this.onChange } />

          <SaveButton show={ updated } />

        </Form>

        <CropperModal 
          fileName='logo' viewMode={0}
          toggle={ this.toggle } 
          uploadImage={ this.updateLogo }
          isOpen={ this.state.showModal } 
          src={ `${LEGACY_URL}${base.logoPaths.logo}` } />

      </TabPane>
    )
  }
}
