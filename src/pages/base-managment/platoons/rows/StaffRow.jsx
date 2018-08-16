import React, { Component } from 'react';
// reactstrap
import { Link } from 'react-router-dom';
import { Row, Col, Button, ButtonGroup, Card, Input } from 'reactstrap';
// styles
import './StaffRow.scss';

class StaffRow extends Component {

  static defaultProps = {
    first: '', last: '', username: '', email: '',
    disconnect: () => {}, admin_id: 0
  }

  disconnect = () => {
    this.props.disconnect( this.props.admin_id )
  }
  
  render() {

    const { admin_id, first, last, username, email } = this.props;
    
    return (
      <Card className='StaffRow'>
        <Row>
          <Col xs='12'>
            <p className='staff-name'>
              Name: <Link to={`/bm/staff/${admin_id}`}>{last}, {first}</Link>
            </p>
          </Col>
          <Col sm='4'>
            <label>Username</label>
            <Input disabled value={username} />
          </Col>
          <Col sm='4'>
            <label>E-Mail</label>
            <Input disabled value={email} />
          </Col>
          <Col sm='4'>
            <label>Actions</label>
            <ButtonGroup>
              <Button color='danger' onClick={ this.disconnect }>
                Disconnect
              </Button>
            </ButtonGroup>
          </Col>
        </Row>
      </Card>
    );
  }
}

export default StaffRow;