import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { ProfileRow, NameRow, DobRow, AddressRow } from './rows';
import PlatoonSelect from 'components/selects/PlatoonSelect';
import CropperModal from 'components/modals/CropperModal';
import BaseSelect from 'components/selects/BaseSelect';
import Select from 'react-select';
// functions
import { missionTypeOptions, findOption } from 'functions/selects';
import { uploadProfile } from 'store/soldiers/operations';
import { loginChanged } from 'functions/login';
import { setTitle } from 'functions/utils';
import { connect } from 'react-redux';

class NewUserPage extends Component {
  // initial state
  state = { 
    cropperModalShow: false, 
    soldier: {
      first: '', last: '', first_he: '', last_he: '', 
      user_city: '', user_state: '', user_phone: '', 
      user_postal: '', user_country: ''
    }
  }
  // lifecylcle - initial component mount
  componentDidMount() { 
    setTitle( 'Create New Soldier' );
    this.setupSoldier();
  }
  componentDidUpdate( prevProps ) {
    if ( loginChanged( this.props.current_login, prevProps.current_login ) ) {
      this.setupSoldier();
    }
  }
  // setup the school_id/class_id on the soldier
  setupSoldier = () => {
    const { code, id } = this.props.current_login;
    if ( code === 'BC' )
      return this.setState({ soldier: { ...this.state.soldier, school_id: id } });
    if ( code === 'TEACHER' )
      return this.setState({ soldier: { ...this.state.soldier, class_id: id } });
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
  // handle react-select dropdown change events
  handleSelectChange = ( id ) => ( option ) => {
    this.handleUpdate( { [id]: option.value } );
  }
  // handle updates to the form
  handleUpdate = ( update ) => {
    const soldier = Object.assign( {}, this.state.soldier, update );
    this.setState({ soldier });
  }
  // upload profile picture
  updateProfile = ( formData ) => {
    this.toggle();
    this.props.uploadProfile( formData )
    .then( response => {
      const soldier = Object.assign({}, this.state.soldier, { ...response.data });
      this.setState({ soldier });
    })
  }
  // validate and submit the user...
  submit = ( event ) => {
    event.preventDefault();
    console.log( this.state.soldier );
    debugger;
  }

  render() {
    const { code } = this.props.current_login;
    const { soldier, cropperModalShow } = this.state;
    const { gender, school_type_id, school_id, class_id } = soldier;
    // generate the mission_type options
    let mission_type_options = missionTypeOptions( gender );

    let baseSelect, platoonSelect;
    
    if ( ['HQ', 'CKIDS-ADMIN'].includes( code ) ) {
      baseSelect = (
        <Col xs='6'>
          <label>Base</label>
          <BaseSelect value={ school_id } onChange={this.handleSelectChange('school_id')} />
        </Col>
      );
    }

    if ( ['HQ', 'CKIDS-ADMIN', 'BC' ].includes( code ) ) {
      platoonSelect = (
        <Col xs={ code === 'BC' ? 12 : 6 }>
          <label>Platoon</label>
          <PlatoonSelect school_id={ school_id } value={ class_id } />
        </Col>
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
                  value={findOption( mission_type_options, school_type_id )} openMenuOnFocus />
              </Col>
              { baseSelect }
              { platoonSelect }
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