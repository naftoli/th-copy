import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Link } from 'react-router-dom';
import { Spinner } from 'components/ui';
import MaskedInput from 'react-text-mask';
import { Select } from 'components/selects';
import { Row, Col, Input, InputGroup, InputGroupAddon, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { loginChanged } from 'functions/login';
import { getPlatoon } from 'store/platoons/operations';
import { deleteAuth, createAuth } from 'store/login/operations';
// data
import masks from 'components/masks';
import StaffRow from './StaffRow';
// styles
import './PlatoonPage.scss';

export class PlatoonPage extends Component {
  // initial state
  state = {
    platoon: {}, updates: {}, loading: true
  }

  emailRef = React.createRef()

  // non-destructivly update the state
  handleUpdate = ( update ) => {
    const platoon = Object.assign( {}, this.state.platoon, update );
    const updates = Object.assign( {}, this.state.updates, update );
    this.setState({ platoon, updates });
  }
  // handle selects
  handleInputChange = ({ target }) => { this.handleUpdate({ [target.name]: target.value }) };
  handleSelectChange = ( option ) => { this.handleUpdate({ [option.id]: option.value }) };

  // load the contents if we do not have any
  componentDidMount(){
    setTitle( 'View/Edit Platoon' );
    this.getPlatoon();
  }

  // if the soldier list is emptied while on the page... then refresh it
  componentDidUpdate( { login } ) {
    if ( loginChanged( this.props.login, login ) ) { this.getPlatoon(); }
  }

  getPlatoon = () => {
    const { match, getPlatoon } = this.props;
    this.setState({ loading: true });
    getPlatoon( match.params.id )
    .then( platoon => { this.setState({ platoon, loading: false }); })
    .catch( error => {
      toast.error( error.message );
      this.setState({ platoon: undefined }); }
    );
  }

  save = () => {
    console.log( this.state.updates );
  }

  disconnect = ( admin_id ) => {
    const { class_id: id } = this.state.platoon;
    this.props.deleteAuth({ admin_id, id, auth: 'class' })
    .then( this.getPlatoon )
    .catch( error => { toast.error( error.message ) } );
  }

  connect = () => {
    const { class_id: id } = this.state.platoon;
    const emailInput = this.emailRef.current;
    // if nothing was entered, focus on the input
    if ( !emailInput.value ) return emailInput.focus();
    // create the connection
    this.props.createAuth({ 
      email: emailInput.value, id, auth: 'class'
    }).then( this.getPlatoon )
    .catch( error => { toast.error( error.message ) } );
  }

  render() {
    if ( this.state.loading ) return <Spinner size='10'/>
    
    const updated = Object.keys( this.state.updates ).length > 0;
    const { class_grade, class_sub, class_teacher, cell, email, staff } = this.state.platoon;
    const inputProps = { onChange: this.handleInputChange };

    let grades = [
      'Pre-school 1', 'Pre-school 2', 'Pre-school 3', 'Pre1a', '1', 
      '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
    ];
    grades = grades.map( grade => ({label: grade, value: grade, id: 'class_grade'}) );

    return (
      <div id='PlatoonPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />

        <p className='title'>Platoon Information</p>
        <div className="alert alert-info">
          Please note that this information is for display and managment purposes only.<br/>
          To add/remove teacher (staff) accounts please look at the Staff tab under Base Managment.
          To edit these accounts please go <Link to='/staff'>here.</Link>
        </div>
        <Row>
          <Col xs={6}>
            <label>Grade</label>
            <Select options={grades} onChange={ this.handleSelectChange }
              value={{ label: class_grade, value: class_grade }} />
          </Col>
          <Col xs={6}>
            <label>Subject</label>
            <Input name='class_sub' value={ class_sub } { ...inputProps } />
          </Col>
          <Col xs={12}>
            <label>Teacher</label>
            <Input name='class_teacher' value={ class_teacher } { ...inputProps } />
          </Col>
          <Col xs={6}>
            <label>Teacher Cell</label>
            <MaskedInput name='cell' value={ cell } { ...inputProps } 
              mask={ masks.phone } className='form-control'/>
          </Col>
          <Col xs={6}>
            <label>Teacher E-Mail</label>
            <Input name='email' value={ email } { ...inputProps } />
          </Col>
          { updated &&
            <Col xs={12} id='save'>
              <Button onClick={ this.save } color='primary'>
                <i className={'fas fa-save'}></i> Save Changes
              </Button>
            </Col>
          }
        </Row>
        {/* show all the staff and manage them */}
        <p className='title'>Connected Staff</p>
        <Row id='connect-new-staff'>
          <Col xs='12'>
            <label>Add staff by E-mail address</label>
            <InputGroup>
              <input placeholder='example@example.com' ref={ this.emailRef } className='form-control'/>
              <InputGroupAddon addonType="append">
                <Button onClick={ this.connect } color='primary' outline tabIndex={0}>
                  <i className={'fas fa-user-plus'}></i> Connect Staff
                </Button>
              </InputGroupAddon>
            </InputGroup>
          </Col>
        </Row>
        { staff.map( (staff, index) => 
          <StaffRow key={index} disconnect={this.disconnect} {...staff} />
        )}
        {/* Debugging */}
        <p className='title'>Debug</p>
        <pre>{ JSON.stringify( this.state.updates, null, 2 ) }</pre>
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { getPlatoon, deleteAuth, createAuth }

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonPage );
