import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { ProfileRow, NameRow, DobRow, AddressRow } from './rows';
import CropperModal from 'components/modals/CropperModal';
import Select from 'react-select';
// functions
import { connect } from 'react-redux';
import { setTitle } from 'functions/utils';
import { uploadProfile } from 'store/soldiers/operations';

class NewUserPage extends Component {
  // initial state
  state = { 
    cropperModalShow: false, 
    soldier: {
      first: '', last: '', first_he: '', last_he: '', 
      user_address1: '', user_city: '', user_state: '',
      user_phone: '', user_postal: '', user_country: ''
    }
  }
  // lifecylcle - initial component mount
  componentDidMount() { 
    setTitle( 'Create New Soldier' );
  }
  // edit profile
  toggle = () => {
    this.setState({ cropperModalShow: !this.state.cropperModalShow });
  }
  // handle change events
  handleChangeEvent = ( event ) => {
    this.handleUpdate( { [event.target.id]: event.target.value } );
  }
  // handle moment dates
  handleDateChange = ( name ) => ( date ) => {
    this.handleUpdate({ [name]: date.format("YYYY-MM-DD HH:mm:ss") });
  }
  // handle dropdown change events
  handleSelectChange = ( id ) => ( option ) => {
    this.handleUpdate( { [id]: option.value } );
  }
  // handle updates to the soldier
  handleUpdate = ( update ) => {
    const soldier = Object.assign( {}, this.state.soldier, update );
    this.setState({ soldier });
  }
  // handle new images
  updateProfile = ( formData ) => {
    this.toggle();
    this.props.uploadProfile( formData )
    .then( response => {
      const soldier = Object.assign({}, this.state.soldier, { ...response.data });
      this.setState({ soldier });
    })
  }
  // submit the user...
  submit = ( event ) => {
    event.preventDefault();
    console.log( this.state.soldier );
    debugger;
  }

  render() {
    const { soldier, cropperModalShow } = this.state;
    const { gender, school_type_id } = soldier;
    // generate the mission_type options
    const findOption = ( options, value ) => options.find( option => option.value === value );
    let mission_type_options = [ 
      { value: 2, label: 'Chabad' }, { value: 12, label: 'Frum' }, { value: 22, label: 'C-Kids' } 
    ];
    if ( gender === 'F' ) {
      mission_type_options = mission_type_options.map( 
        option => Object.assign( {}, option, { value: option.value + 1 } )
      );
    }

    return (
      <form id='NewUserPage' onSubmit={this.submit}>
        <Row id='image-row' style={{alignItems: 'center'}}>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
            <p className='title'>Required Personal Information</p>
            
            <NameRow soldier={ soldier } onChange={ this.handleChangeEvent } required />
            
            <DobRow soldier={ soldier } onChange={ this.handleDateChange('dob') } required>
              <Col xs='6'>
                <label>Mission Type</label>
                <Select options={mission_type_options} onChange={this.handleSelectChange('school_type_id')}
                  value={findOption( mission_type_options, school_type_id )}/>
              </Col>
            </DobRow>

          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>
            <ProfileRow soldier={ soldier } toggle={ this.toggle } 
              onChange={ this.handleUpdate } />
          </Col>
        </Row>

        <AddressRow soldier={ soldier } onChange={ this.handleChangeEvent } />

        <Button color='primary'>Create Soldier</Button>

        <CropperModal isOpen={ cropperModalShow } 
          toggle={ this.toggle } uploadImage={ this.updateProfile } />
      </form>
    )
  }
}

const mapStateToProps = ( state ) => ({
  current_login: state.login.current_login
});

export default connect( mapStateToProps, { uploadProfile } )( NewUserPage );