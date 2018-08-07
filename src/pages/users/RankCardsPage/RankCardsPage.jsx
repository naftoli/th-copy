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
      { rank: 'Private', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 1,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Sergeant', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 2,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Sergeant Major', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 3,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Second Lieutenant', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 4,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'First Lieutenant', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 5,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Captain', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 6,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Major', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 7,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'Colonel', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 8,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: 'General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 9,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: '1* General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 10,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: '2* General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 11,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: '3* General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 12,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: '4* General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 13,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },
      { rank: '5* General', user_serial: '7761797', first: 'No', last: 'Name', barcode: 37170565993180946000, rank_ord: 14,
        school_logo: '/file_view.php?id=2275195517', profilePicture: '/mobile/reg/img/20180525193812.png' },

    ]
    return (
      <div id='RankCardsPage'>
        <Callout title='Soldier Rank Cards' className='no-print'>
          Please note that Headquarters will likely print and ship permenent ID cards to you.<br/>
          If you wish to print your own please make sure you can print 3.37in x 2.13in ( 8.56cm x 5.41cm )
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
