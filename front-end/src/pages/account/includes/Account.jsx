import React from 'react';
// components
import { Row, Col, Button } from 'reactstrap';

export const Account = ({ name, role, disconnect, type, id }) => {

  const onClick = () => {
    disconnect(type, id);
  }

  return (
    <div className='Account'>
      <Row>
        <Col sm={4}>
          <label>Connects To</label>
          <span className='name'>{name}</span>
        </Col>

        <Col sm={4}>
          <label>Role</label>
          <span className='name'>{role}</span>
        </Col>

        <Col sm={4}>
          <Button outline color='danger' onClick={onClick}>
            Remove Access
          </Button>
        </Col>
      </Row>
    </div>
  );
}
