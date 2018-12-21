import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import Child from './misc/Child';
import { Page404 } from 'pages/errors';
import { AddressRow } from 'components/rows';
import { Select, PhoneNumber } from 'components/inputs';
import { LoadingScreen, ProfilePicture, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button, InputGroup, InputGroupAddon } from 'reactstrap';
// functions
import memoize from 'memoize-one';
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { mobileLogin, isHQ } from 'functions/login';
import { getChildOptions } from './misc/functions';
import { getParents, removeChild, addChild } from 'store/base/parents/operations';
import { findOption } from 'functions/selects';

class ParentPage extends Component {

  state = {
    user_serial: false
  }

  componentDidMount() {
    setTitle( 'Parent Account' )
    if ( this.props.parents.length === 0 ) {
      this.props.getParents();
    }
  }

  findParent = memoize( ( parents, admin_id ) => {
    return parents.find( parent => parent.admin_id === admin_id );
  } );

  getParent = () => {
    const admin_id = parseInt( this.props.match.params.id, 10 );
    return this.findParent( this.props.parents, admin_id );
  }

  loginToParent = () => {
    const parent = this.getParent();
    mobileLogin( parent.key );
  }

  removeChild = ( user_serial ) => {
    const parent = this.getParent();
    this.props.removeChild( parent.admin_id, user_serial )
    .catch( error => { toast.error( error.message ) });
  }

  addChild = () => {
    const { user_serial } = this.state;
    const parent = this.getParent();

    if ( !user_serial ) {
      toast.error('Please select a soldier to add as a child.');
    } else {
      this.props.addChild( parent.username, user_serial )
      .then( () => this.setState({ user_serial: false }) )
      .catch( error => toast.error( error.message ) );
    }
  }

  onChildChange = ( option ) => {
    this.setState({ user_serial: option && option.value });
  }

  render() {
    const { user_serial } = this.state;
    const { loading, login } = this.props;
    // get the parent information
    const parent = this.getParent();
    // if we have nothing, end the function here and show a LoadingScreen
    if ( loading && !parent )
      return <LoadingScreen />;
    else if ( !parent )
      return <Page404 />;
    // get the info from the parent
    const { 
      father_pic, mother_pic, father, mother, last,
      username, cell, email, children, ...address
    } = parent;

    const options = getChildOptions( this.props.availableChildren );

    // and render the page
    return (
      <div id='ParentPage'>
        <p className='title'>Account Information</p>
        {/* Profile Pictures */}
        <Row id='profile'>
          <Col xs={6}>
            <ProfilePicture src={ father_pic } alt='father' />
            <p>{father} {last}</p>
          </Col>
          <Col xs={6}>
            <ProfilePicture src={ mother_pic } alt='mother' />
            <p>{mother} {last}</p>
          </Col>
        </Row>
        {/* Account information */}
        <Row>
          <Col sm={{size: 6, order: 1}}>
            <label>Username</label>
            <Input value={ username } disabled />
          </Col>
          <Col xs={{size: 12, order: 12}} sm={{size: 6, order: 2}}>
            <Button color='primary' id='login' onClick={ this.loginToParent }>
              <FontAwesome icon='sign-in-alt' /> Login to parent account.
            </Button>
          </Col>
          <Col sm={{size: 6, order: 3}}>
            <label>Cell Phone</label>
            <PhoneNumber value={ cell } disabled />
          </Col>
          <Col sm={{size: 6, order: 4}}>
            <label>E-Mail Address</label>
            <Input value={ email } disabled />
          </Col>
        </Row>

        <AddressRow disabled { ...address } prefix='admin_' title='Address' />

        <p className='title'>Children</p>
        <Row id='add-child'>
          { !isHQ( login.code ) &&
            <Col xs='12'>
              <InputGroup>
                <Select  options={ options }
                  onChange={ this.onChildChange }
                  value={ findOption( options, user_serial ) || false }
                  isClearable className='react-select form-control' />

                <InputGroupAddon addonType="append">
                  <Button onClick={ this.addChild } color='primary' outline tabIndex={0}>
                    <FontAwesome icon='user-plus' /> Add Child
                  </Button>
                </InputGroupAddon>
              </InputGroup>
            </Col>
          }
        </Row>
        
        { parent.children.map( (child, index) => 
          <Child key={index} {...child} onRemove={ this.removeChild }/>
        ) }
      </div>
    );
  }
}

const mapStateToProps = ( { base, login } ) => ({
  login: login.current_login,
  parents: base.parents.parents,
  loading: base.parents.loading,
  availableChildren: base.parents.children,
})

const mapDispatchToProps = {
  getParents, removeChild, addChild
}

export default connect( 
  mapStateToProps, mapDispatchToProps 
)( ParentPage );
