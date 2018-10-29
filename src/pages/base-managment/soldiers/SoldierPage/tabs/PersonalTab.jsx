import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Row, Col, TabPane, Input } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { RegistrationRow } from '../rows';
import { DobCol, NameRow, ProfileRow, BasePlatoonRow } from '../../components';

class PersonalTab extends Component {
  // initial state
  state = {
    cropperModalShow: false
  }
  // handle updates from events
  handleChange = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.value } );
  }
  // when an input changes, update the soldier in the state
  onInputChange = ({ target }) =>
    this.props.handleChange({ [target.id]: target.value });
  // only the dob is changed at the moment
  onDateChange = date =>
    this.props.handleChange({
      dob: date && date.format('YYYY-MM-DD HH:mm:ss')
    });
  // selects do not provide a key by default
  onSelectChange = key => option =>
    this.props.handleChange({ [key]: option ? option.value : option });

  // edit profile
  toggle = () => {
    this.setState({ cropperModalShow: !this.state.cropperModalShow });
  }
  // close the modal and update the profile picture
  updateProfile = ( formData ) => {
    this.props.updateProfile( formData );
    this.toggle();
  }

  render(){
    const { 
      login, soldier, tabId, updated, 
      onSubmit, onValidChange 
    } = this.props;
    let { user_serial, barcode, profilePicture } = soldier;
    // link to the old website if we have a profile picture
    const profile_picture = profilePicture ? `${LEGACY_URL}${profilePicture}` : '';
    // render form
    return (
    <TabPane tabId={ tabId }>
      <Form id='PersonalTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>
        
        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>

            { user_serial && <h4>Serial #: {user_serial}</h4> }
            { barcode && <h4>Barcode: {barcode}</h4> }

            <NameRow soldier={ soldier } onChange={ this.handleChange } />

          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>

            <ProfileRow
              src={ profilePicture }
              gender={ soldier.gender }
              onImageClick={ this.toggle }
              onGenderChange={ this.onInputChange } />

          </Col>
        </Row>

        <Row>
          <DobCol
            dob={ soldier.dob }
            onChange={ this.onDateChange } />

          <Col sm='6' dir='rtl'>
            <label>יום הולדת</label>
            <Input disabled value={ soldier.dob_he }/>
          </Col>
        </Row>
        
        <BasePlatoonRow
          isClearable
          code={ login.code }
          classId={ soldier.class_id }
          schoolId={ soldier.school_id }
          onChange={ this.onSelectChange } />

        <AddressRow
          showPhone
          prefix='user_'
          { ...soldier }
          onChange={ this.handleChange } />

        <RegistrationRow soldier={ soldier } />

        <SaveButton show={ updated } />

      </Form>
      
      <CropperModal isOpen={ this.state.cropperModalShow } src={ profile_picture } 
          toggle={ this.toggle } uploadImage={ this.updateProfile } />

    </TabPane>
    );
  }
}

export default PersonalTab;
