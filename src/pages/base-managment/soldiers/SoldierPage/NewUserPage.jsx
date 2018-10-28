import React, { Component } from 'react';
// components
import { FontAwesome } from 'components/ui';
import { AddressRow } from 'components/rows';
import { Row, Col, Button } from 'reactstrap';
import { ProfileRow, NameRow, DobRow } from './rows';
import CropperModal from 'components/modals/CropperModal';
import { PlatoonSelect, BaseSelect, Select } from 'components/inputs';
// functions
import { connect } from 'react-redux';
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
import { missionTypeOptions, findOption } from 'functions/selects';
import { uploadProfile, createSoldier, getSoldiers } from 'store/base/soldiers/operations';

const initialSoldier = {
  first: '', last: '', first_he: '', last_he: '', 
  user_city: '', user_state: '', user_phone: '', 
  user_postal: '', user_country: ''
}

class NewUserPage extends Component {
  // initial state
  state = { 
    loading: false, 
    cropperModalShow: false, 
    soldier: initialSoldier
  }
  // lifecylcle - initial component mount
  componentDidMount() { 
    setTitle( 'Create New Soldier' );
    this.setupSoldier();
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
      const soldier = Object.assign({}, this.state.soldier, { ...response });
      this.setState({ soldier });
    })
  }

  validateSoldier = () => {
    const { soldier } = this.state;
    const { code } = this.props.current_login;
    return new Promise( (resolve, reject) => {
      // validate DOB
      if ( !soldier.dob ) { reject( new Error('Please select a valid Date of Birth') ); }
      if ( !soldier.school_type_id ) { reject( new Error('Please select a Mission Type') ); }
      if ( !soldier.gender ) { reject( new Error('Please select a Gender') ); }
      if ( isAdmin( code ) && !soldier.school_id ) {
        reject( new Error('Please select a Base') );
      }
      if ( isBC( code ) && !soldier.class_id ) {
        reject( new Error('Please select a Platoon') );
      }
      resolve( soldier );
    });
  }

  // validate and submit the user...
  submit = ( event ) => {
    event.preventDefault();
    if ( this.state.loading ) return false;
    this.validateSoldier()
    .then( soldier => {
      this.setState({ loading: true });
      return this.props.createSoldier( soldier );
    })
    .then( soldier => {
      this.setState({ loading: false });
      if ( response.success )
        this.props.getSoldiers(); // refresh the list of soldiers
        this.props.history.push(`/bm/soldiers/${response.data.user_id}`);
    })
    .catch( error => {
      toast.error( error.message );
      this.setState({ loading: false });
    })
  }

  render() {
    const { code } = this.props.current_login;
    const { soldier, cropperModalShow, loading } = this.state;
    const { gender, school_type_id, school_id, class_id } = soldier;
    // generate the mission_type options
    let mission_type_options = missionTypeOptions( gender );
    // show the dropdown needed for the current login
    let baseSelect, platoonSelect;
    if ( isAdmin( code ) ) {
      baseSelect = (
        <Col xs={ 6 }>
          <label>Base</label>
          <BaseSelect value={ school_id } onChange={this.handleSelectChange('school_id')} />
        </Col>
      );
    }
    if ( isBC( code ) ) {
      platoonSelect = (
        <Col xs={ isAdmin( code ) ? 6  : 12 }>
          <label>Platoon</label>
          <PlatoonSelect schoolId={ school_id } value={ class_id } 
            onChange={this.handleSelectChange('class_id')} />
        </Col>
      );
    }
    // render the page
    return (
      <form id='NewUserPage' onSubmit={this.submit}>
        <Row id='image-row'  style={{alignItems: 'center'}}>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>
            <p className='title'>Required Personal Information</p>
            
            <NameRow soldier={ soldier } onChange={ this.handleChangeEvent } required />
            
            <DobRow soldier={ soldier } onChange={ this.handleDateChange('dob') } required>

              <Col xs='6'>
                <label>Mission Type</label>
                <Select options={mission_type_options} onChange={this.handleSelectChange('school_type_id')}
                  value={findOption( mission_type_options, school_type_id )} />
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

        <AddressRow {...soldier} showPhone prefix='user_' onChange={ this.handleChangeEvent } />

        <Button color='primary' style={{ width: '100%' }}>
          <FontAwesome icon='save' />
          { loading ? ' Creating...' : ' Create Soldier'}
        </Button>

        <CropperModal isOpen={ cropperModalShow } 
          toggle={ this.toggle } uploadImage={ this.updateProfile } />
      </form>
    )
  }
}

const mapStateToProps = ( state ) => ({
  current_login: state.login.current_login
});

export default connect( mapStateToProps, {
  uploadProfile, createSoldier, getSoldiers 
} )( NewUserPage );