import React, { Component } from 'react';
// components
import { Link } from 'react-router-dom';
import { ProfilePicture } from 'components/ui';
import { TabPane, Row, Col } from 'reactstrap';

const Soldier = props => {
  const { user_id, user_serial, first, last, profilePicture, rank } = props;
  return (
    <Row className='soldier'>
      <Col xs={ 3 } xl={ 2 }>
        <ProfilePicture src={ profilePicture } rank={ rank.rank_ord } />
      </Col>
      <Col xs={ 9 } xl={ 10 }>
        <Row>
          <Col xs={ 12 } xl={ 4 }>
            <strong>Serial Number: </strong>
            <p className='s-number'><Link to={`/bm/soldiers/${user_id}`}>{user_serial}</Link></p>
          </Col>
          <Col xs={ 12 } xl={ 8 }>
            <strong>Full Name: </strong>
            <p><Link to={`/bm/soldiers/${user_id}`}>{ rank.name } {first} {last}</Link></p>
          </Col>
        </Row>
      </Col>
    </Row>
  );
}

export class SoldiersTab extends Component {

  render(){
    const { users, tabId } = this.props;

    return (
      <TabPane tabId={ tabId } id='SoldiersTab' >

        <h2>{ users.length } Soldiers</h2>

        <div id='soldiers'>
          { users.map( ( user, index ) => 
            <Soldier key={index} {...user} />
          )}
        </div>

      </TabPane>
    );
  }
}
