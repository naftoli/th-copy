import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Prompt } from 'react-router';
import { Link, Redirect } from 'react-router-dom';
import { Spinner } from 'components/ui';
import { Row, Col, InputGroup, InputGroupAddon, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { loginChanged } from 'functions/login';
import { StaffRow, PlatoonRow } from '../rows/';
import { getPlatoon, updatePlatoon } from 'store/platoons/operations';
import { deleteAuth, createAuth } from 'store/login/operations';
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
    const { updates, platoon } = this.state;
    this.props.updatePlatoon( platoon.class_id, updates )
    .then( platoon => this.setState({ platoon, updates: {} }) );
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
    if ( this.state.platoon === undefined ) return <Redirect to='/platoons' />;
    if ( this.state.loading ) return <Spinner size='10'/>
    
    const { staff } = this.state.platoon;
    const inputProps = { onChange: this.handleInputChange };
    const selectProps = { onChange: this.handleSelectChange };
    const updated = Object.keys( this.state.updates ).length > 0;

    return (
      <div id='PlatoonPage'>
        <Prompt when={ updated } message="You have unsaved changes. Are you sure you want to leave?" />

        <p className='title'>Platoon Information</p>
        <div className="alert alert-info">
          Please note that this information is for display and managment purposes only.<br/>
          To add/remove teacher (staff) accounts please look at the Staff tab under Base Managment.
          To edit these accounts please go <Link to='/staff'>here.</Link>
        </div>

        <PlatoonRow platoon={this.state.platoon} 
          inputProps={ inputProps } selectProps={ selectProps } />
        { updated &&
          <Row>
            <Col xs={12} id='save'>
              <Button onClick={ this.save } color='primary'>
                <i className={'fas fa-save'}></i> Save Changes
              </Button>
            </Col>
          </Row>
        }
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
      </div>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { 
  getPlatoon, updatePlatoon, 
  deleteAuth, createAuth
}

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonPage );
