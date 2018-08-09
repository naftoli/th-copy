import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { Spinner } from 'components/ui';
import MaskedInput from 'react-text-mask';
import { Select } from 'components/selects';
import { Row, Col, Button, ButtonGroup, Input, Alert } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { loginChanged } from 'functions/login';
import { getPlatoon } from 'store/platoons/operations';
// data
import masks from 'components/masks';
// styles
// import './PlatoonPage.scss';

export class PlatoonPage extends Component {
  // initial state
  state = {
    platoon: {}, updates: {}, loading: true
  }

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

  render() {
    if ( this.state.loading ) return <Spinner size='10'/>
    
    const { 
      class_grade, class_sub, class_teacher, cell, email 
    } = this.state.platoon;
    const inputProps = { onChange: this.handleInputChange };

    let grades = [
      'Pre-school 1', 'Pre-school 2', 'Pre-school 3', 'Pre1a', '1', 
      '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'
    ];
    grades = grades.map( grade => ({label: grade, value: grade, id: 'class_grade'}) );

    return (
      <div id='PlatoonPage'>
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
        </Row>
        <p className='title'>Staff</p>
        <p className='title'>Debug</p>
        <pre>{ JSON.stringify( this.state, null, 2 ) }</pre>
      </div>
    )
  }
}

const mapStateToProps = ( { platoons, login } ) => ({
  login: login.current_login
})

const mapDispatchToProps = { getPlatoon }

export default connect( mapStateToProps, mapDispatchToProps )( PlatoonPage );
