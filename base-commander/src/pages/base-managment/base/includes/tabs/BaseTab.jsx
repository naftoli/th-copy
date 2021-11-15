import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { BaseRow } from '../rows';
import { Form, Radio } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { BaseLogo } from 'components/ui';
import { SaveButton } from 'components/buttons';
import { Row, Col, Input, TabPane } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
// functions
import { onInputChange } from 'functions/events';

export class BaseTab extends Component {

  defaultLogoGender = () => {
    const base = this.props.base
    return base.school_gender === "F" ? 'girls'
      : base.logoPaths['boys'] ? 'boys'
      : base.logoPaths['girls'] ? 'girls'
      : 'boys'
  }

  state = {
    showModal: false,
    logoGender: this.defaultLogoGender()
  }

  onChange = onInputChange( this.props.onUpdate );

  toggleLogoModal = () => this.setState(prevState => ({
    ...prevState,
    showModal: !prevState.showModal
  }));

  toggleLogoGender = (e) => {
    const value = e.target.value
    return this.setState(prevState => ({ ...prevState, logoGender: value }))
  };

  updateLogo = formData => {
    this.toggleLogoModal();
    this.props.updateBase( formData );
  }

  addDaySchoolCampaigns = (e) => {
    e.preventDefault()
    const school_id = this.props.base.school_id
    this.props.addCampaigns(school_id, 'day school')
        .then( info => {
          alert('Day School Campaigns Added!')
        })
        .catch( info => {
          alert(info.error)
        })
  }

  render(){
    const { base, onUpdate, tabId, updated, onSubmit, onValidChange } = this.props;
    const  { logoGender } = this.state

    const logoGenderProps = { name: 'logoGender', onChange: this.toggleLogoGender }

    return (
      <TabPane tabId={ tabId }>
        <Form id='BaseTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>
          <Row id='image-row'>
            <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
              <h3>Base #{base.school_number}</h3>

              <BaseRow
                { ...base }
                onUpdate={ onUpdate } />

              <Row>
                <Col>
                  <button onClick={ this.addDaySchoolCampaigns }>Add Day School Campaigns</button>
                </Col>
              </Row>

            </Col>
            <Col id='logo' xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
              <Row>
                <Col xs={ 12 } className="mb-2">
                  <BaseLogo
                    onClick={ this.toggleLogoModal }
                    src={ base.logoPaths[logoGender] || base.logoPaths.logo} />
                </Col>
              </Row>
              <Row>
                <Col xs={ 12 }>
                  <Radio value='boys' {...logoGenderProps} checked={ logoGender === 'boys' }>
                    Boys
                  </Radio>

                  <Radio value='girls' {...logoGenderProps} checked={ logoGender === 'girls' }>
                    Girls
                  </Radio>
                </Col>
              </Row>
            </Col>
          </Row>

          <AddressRow { ...base } showPhone prefix='school_' onChange={ this.onChange } />

          <p className='title'>Notes</p>
          <Input type="textarea" name='notes' rows='6'
            value={ base.notes || '' } onChange={ this.onChange } />

          <SaveButton show={ updated } />

        </Form>

        <CropperModal
          fileName={`logo_${logoGender}`} viewMode={0}
          toggle={ this.toggleLogoModal }
          uploadImage={ this.updateLogo }
          isOpen={ this.state.showModal }
          src={ `${LEGACY_URL}${base.logoPaths[logoGender] || base.logoPaths.logo}` } />

      </TabPane>
    )
  }
}
