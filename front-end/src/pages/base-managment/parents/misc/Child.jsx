import React from 'react';
import { FontAwesome } from 'components/ui';
import { Link } from 'react-router-dom';
import { Row, Col, Button } from 'reactstrap';

const Child = ({ first, last, user_id, user_serial, onRemove }) => {

  const remove = () => {
    onRemove(user_serial);
  }

  return (
    <Row className='child'>
      <Col xs={6} sm={{ size: 3, order: 1 }}>
        <strong>Serial Number: </strong>
        <p><Link to={`/bm/soldiers/${user_id}`}>{user_serial}</Link></p>
      </Col>
      <Col xs={{ size: 12, order: 12 }} sm={{ size: 5, order: 2 }}>
        <strong>Full Name: </strong>
        <p><Link to={`/bm/soldiers/${user_id}`}>{first} {last}</Link></p>
      </Col>
      <Col xs={6} sm={{ size: 4, order: 3 }}>
        <strong>Actions: </strong>
        <Button color='danger' onClick={remove}>
          <FontAwesome icon='trash-alt' regular /> Remove From Account
        </Button>
      </Col>
    </Row>
  )
}

export default Child;