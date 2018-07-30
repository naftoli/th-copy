import React, { Component } from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col } from 'reactstrap';
import ProfilePicture from 'components/ui/ProfilePicture';
import Radio from 'components/ui/Radio';
import CropperModal from 'components/modals/CropperModal';
import { AddressRow, RegistrationRow, NameRow, DobRow } from '../rows';

class PersonalTab extends Component {
  // initial state
  state = { cropperModalShow: false }
  // handle updates from events
  handleChange = ( event ) => {
    this.props.handleChange( { [event.target.id]: event.target.value } );
  }
  // handle the gender change
  genderChange = ( event ) => {
    const { school_type_id } = this.props.soldier;
    this.props.handleChange({
      [event.target.id]: event.target.value,
      school_type_id: school_type_id + ( event.target.value === 'M' ? -1 : 1 )
    });
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
  updateProfile = ( formData ) => {
    this.toggle();
    this.props.updateProfile( formData );
  }

  render(){
    const soldier = this.props.soldier;
    let { user_serial, barcode, profilePicture, currentRank, gender  } = soldier;
    const profile_picture = profilePicture ? `${LEGACY_URL}${profilePicture}` : '';
    const rank = currentRank ? currentRank.rank : undefined;
    // render form
    return (
      <div id='PersonalTab'>
        <Row id='image-row'>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <Row>
              <Col xs='3' sm='12'>
                <ProfilePicture src={ profile_picture } className='inline-profile' 
                  rank={ rank } onClick={ this.toggle } />
              </Col>
              <Col xs='9' sm='12'>
                <label>Gender</label>
                <div id='gender-row'>
                  <Radio type='radio' name='gender' id='gender' value='M' 
                      checked={ gender === 'M' } onChange={ this.genderChange }>
                    Male <i className='fas fa-male'></i>
                  </Radio>
                  <Radio type='radio' name='gender' id='gender' value='F'
                      checked={ gender === 'F' } onChange={ this.genderChange }>
                    Female <i className='fas fa-female'></i>
                  </Radio>
                </div>
              </Col>
            </Row>
          </Col>
          <Col xs='12' sm='8' lg='9' xl='10'>
            { user_serial && <h4>Serial #: {user_serial}</h4> }
            { barcode && <h4>Barcode: {barcode}</h4> }

            <NameRow soldier={ soldier } onChange={ this.handleChange } />
            <DobRow soldier={ soldier } onChange={ this.dateChange('dob') } show_he />
          </Col>
        </Row>

        <AddressRow soldier={ soldier } onChange={ this.handleChange } />

        <RegistrationRow soldier={ soldier } />

        <CropperModal isOpen={ this.state.cropperModalShow } src={ profile_picture } 
          toggle={ this.toggle } uploadImage={ this.updateProfile } />
      </div>
    );
  }
}

export default PersonalTab;