import React, { Component } from 'react';
// import { connect } from 'react-redux';
// components
import { Callout } from 'components/ui';
import RankCard from './RankCard';
import { Row, Col, Button, ButtonGroup } from 'reactstrap'; 
// functions
import { loginChanged } from 'functions/login';
import { setTitle } from 'functions/utils';
// styles
import './RankCardsPage.scss';

export class RegistrationPage extends Component {
  state = {}
  
  componentDidMount(){ 
    setTitle('Soldier Rank Cards');
  }

  componentDidUpdate( prevProps ) {
    if ( loginChanged( this.props.login, prevProps.login ) ) {
      console.log( 'loginChanged' );
    }
  }

  render() {
    const users = [
      { rank: 'Private', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' }
    ]
    return (
      <div id='RankCardsPage'>
        <Callout title='Soldier Rank Cards' className='no-print'>
          Please note that Headquarters will likely print and ship permenent ID cards to you.<br/>
          If you wish to print your own please make sure you can print 3.5in x 2in ( 8.89cm x 5.08cm)
        </Callout>
        <Row className='no-print'>
          <Col xs='12'>
            
          </Col>
        </Row>
        <div id='rank-cards'>
          { users.map( (user, index) => <RankCard user={user} key={index} /> ) }
        </div>
      </div>
    )
  }
}

// const mapStateToProps = ( { login, soldiers } ) => ({
//   login: login.current_login,
//   soldiers: soldiers.registration_soldiers,
//   loading: soldiers.loading
// });

export default RegistrationPage;
