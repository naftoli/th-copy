import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { PlatoonRow } from '../rows/';
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect } from 'components/selects';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { createPlatoon } from 'store/platoons/operations';
// styles
// import './PlatoonPage.scss';

export class NewPlatoonPage extends Component {
  // initial state
  state = { platoon: {
    school_id: false, class_sub: '', class_teacher: '', cell: '', email: ''
  }}

  emailRef = React.createRef()

  // non-destructivly update the state
  handleUpdate = ( update ) => {
    this.setState({ platoon: Object.assign( {}, this.state.platoon, update ) });
  }
  // handle selects
  handleInputChange = ({ target }) => { this.handleUpdate({ [target.name]: target.value }) };
  handleSelectChange = ( option ) => { this.handleUpdate({ [option.id]: option.value }) };
  handleBaseChange = ( option ) => { this.handleUpdate({ school_id: option.value }) };

  // load the contents if we do not have any
  componentDidMount(){ setTitle( 'Create New Platoon' ); }

  save = ( e ) => {
    e.preventDefault();
    const { platoon } = this.state;
    if ( !platoon.class_grade ) return toast.error( 'Cannot create Platoon without grade.' );
    this.props.createPlatoon( platoon )
    .then( platoon => {
      this.props.history.push(`/platoons/${platoon.class_id}`);
    });
  }

  render() {
    const { code } = this.props.login;
    const inputProps = { onChange: this.handleInputChange };
    const selectProps = { onChange: this.handleSelectChange };

    let baseSelect;
    if ( ['HQ', 'CKIDS-ADMIN'].includes( code ) ) {
      baseSelect = (
        <Row>
          <Col xs='12'>
            <label>Base</label>
            <BaseSelect value={ this.state.platoon.school_id } 
              onChange={ this.handleBaseChange } />
          </Col>
        </Row>
      );
    }

    return (
      <form id='NewPlatoonPage' onSubmit={ this.save }>
        <p className='title'>Platoon Information</p>

        { baseSelect }

        <PlatoonRow platoon={this.state.platoon} 
          inputProps={ inputProps } selectProps={ selectProps } />
        
        <Row>
          <Col xs={12} id='save'>
            <Button color='primary'>
              <i className={'fas fa-save'}></i> Save Changes
            </Button>
          </Col>
        </Row>

        <p className='title'>Debug</p>
        <pre>{ JSON.stringify( this.state.platoon, null, 2 ) }</pre>
      </form>
    )
  }
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

const mapDispatchToProps = { createPlatoon }

export default connect( mapStateToProps, mapDispatchToProps )( NewPlatoonPage );
