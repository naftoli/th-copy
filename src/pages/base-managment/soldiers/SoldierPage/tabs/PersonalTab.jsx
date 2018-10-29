import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Form } from 'components/inputs';
import { AddressRow } from 'components/rows';
import { SaveButton } from 'components/buttons';
import { Row, Col, TabPane } from 'reactstrap';
import CropperModal from 'components/modals/CropperModal';
import { ProfileRow, DobRow, RegistrationRow } from '../rows';
import { NameRow } from '../../components';

class PersonalTab extends Component {
  // initial state
  state = { cropperModalShow: false }
  // handle updates from events
  handleChange = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.value } );
  }
  // format dates
  dateChange = ( name ) => ( date ) => {
    this.props.handleChange(
      { [name]: date ? date.format("YYYY-MM-DD HH:mm:ss") : date }
    )
  }
  // edit profile
  toggle = () => {
    this.setState({ cropperModalShow: !this.state.cropperModalShow });
  }
  // close the modal and update the profile picture
  updateProfile = ( formData ) => {
    this.toggle();
    this.props.updateProfile( formData );
  }

  render(){
    const { soldier, tabId, updated, onSubmit, onValidChange } = this.props;
    let { user_serial, barcode, profilePicture } = soldier;
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

            <DobRow showHe
              soldier={ soldier }
              onChange={ this.dateChange('dob') } />

          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <ProfileRow soldier={ soldier } toggle={ this.toggle } 
              onChange={ this.props.handleChange } />
          </Col>
        </Row>

        <AddressRow { ...soldier } showPhone prefix='user_' onChange={ this.handleChange } />

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
