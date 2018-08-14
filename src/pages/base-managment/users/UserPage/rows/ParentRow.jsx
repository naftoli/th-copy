import React, { Component } from 'react';
import { COOKIES, EXPIRES } from 'store/constants';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input, InputGroup, InputGroupAddon, Button } from 'reactstrap';
import { FontAwesome } from 'components/ui';
// functions
import { connect } from 'react-redux';
import { removeChild, addChild } from 'store/parents/operations';
import { toast } from 'react-toastify';

const styles = {
  buttonColumn: {
    marginTop: '.7em',
    textAlign: 'center'
  }
}

export class ParentRow extends Component {
  // props we are expecting for this component
  static defaultProps = {
    parentAccount: false, // object with first, last, phone, email and key props
    userId: false, // integer
    refresh: () => {} // function when something worth refreshing happens
  }
  // handle parent username input
  state = { username: '' }
  usernameRef = React.createRef();
  updateUsername = ( event ) => { this.setState({username: event.target.value}) }

  changeLogin = () => {
    const { key } = this.props.parentAccount;
    COOKIES.set('admin', key, { path: '/', EXPIRES } );
    window.open( `${LEGACY_URL}/mobile/reg/parent_detail.html`, '_blank' ).focus();
  }

  addToAccount = () => {
    const { username } = this.state;
    const { userId: user_id, addChild } = this.props;
    // make sure something was typed
    if ( username === '' && this.usernameRef.current ) 
      return this.usernameRef.current.focus();
    // add child and update the UI based on it's result
    addChild( username, user_id )
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) });
  }

  remove = ( event ) => {
    const { userId: user_id, parentAccount } = this.props;
    const admin_id = parseInt( parentAccount.admin_id, 10 );
    this.props.removeChild( admin_id, user_id )
    .then( this.props.refresh )
    .catch( error => { toast.error( error.message ) });
  }

  render() {
    if ( this.props.parentAccount ) {
      const { first, last, phone, email } = this.props.parentAccount;
      return (
        <Row>
          <Col xs='12'>
            <label>Name</label>
            <Input disabled value={ first + ' ' + last } />
          </Col>
          <Col xs='12' sm='6'>
            <label>Phone Number</label>
            <Input disabled value={ phone } />
          </Col>
          <Col xs='12' sm='6'>
            <label>E-mail</label>
            <Input disabled value={ email } />
          </Col>
          <Col xs='12' sm='6' style={styles.buttonColumn}>
            <Button color='primary' onClick={ this.changeLogin }>
              Login to Parent Account
            </Button>
          </Col>
          <Col xs='12' sm='6' style={styles.buttonColumn}>
            <Button color='danger' onClick={ this.remove }>
              Remove From Parent Account
            </Button>
          </Col>
        </Row>
      );
    } else { // if we do not have a parent account
      return (
        <Row>
          <Col xs='12'>
            <label>Add to parent account by parents username</label>
            <InputGroup>
              <input onChange={this.updateUsername} value={this.state.username} 
                placeholder='Username' ref={ this.usernameRef } className='form-control'/>
              <InputGroupAddon addonType="append">
                <Button onClick={ this.addToAccount } color='primary' outline tabIndex={0}>
                  <FontAwesome icon='user-plus' /> Add Soldier
                </Button>
              </InputGroupAddon>
            </InputGroup>
          </Col>
        </Row>
      );
    }
  }
}

export default connect(null, { removeChild, addChild })( ParentRow );
