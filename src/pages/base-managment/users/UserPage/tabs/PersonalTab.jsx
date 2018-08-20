import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col } from 'reactstrap';
import { AddressRow } from 'components/rows';
import CropperModal from 'components/modals/CropperModal';
import { ProfileRow, NameRow, DobRow, RegistrationRow } from '../rows';

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
      { [name]: date.format("YYYY-MM-DD HH:mm:ss") }
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
    const soldier = this.props.soldier;
    let { user_serial, barcode, profilePicture } = soldier;
    const profile_picture = profilePicture ? `${LEGACY_URL}${profilePicture}` : '';
    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
            { user_serial && <h4>Serial #: {user_serial}</h4> }
            { barcode && <h4>Barcode: {barcode}</h4> }

            <NameRow soldier={ soldier } onChange={ this.handleChange } />

            <DobRow soldier={ soldier } showHe onChange={ this.dateChange('dob') } />

          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <ProfileRow soldier={ soldier } toggle={ this.toggle } 
              onChange={ this.props.handleChange } />
          </Col>
        </Row>

        <AddressRow { ...soldier } prefix='user_' onChange={ this.handleChange } />

        <RegistrationRow soldier={ soldier } />

        <CropperModal isOpen={ this.state.cropperModalShow } src={ profile_picture } 
          toggle={ this.toggle } uploadImage={ this.updateProfile } />

      </div>
    );
  }
}

export default PersonalTab;
