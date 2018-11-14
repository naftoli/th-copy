import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';

export class Account extends Component {

  onClick = () => {
    this.props.disconnect( this.props.type, this.props.id );
  }

  render() {
    const { name, role } = this.props;

    return (
      <div className='Account'>
        <Row>
          <Col sm={4}>
            <label>Connects To</label>
            <span className='name'>{ name }</span>
          </Col>

          <Col sm={4}>
            <label>Role</label>
            <span className='name'>{ role }</span>
          </Col>
          
          <Col sm={4}>
            <Button outline color='danger' onClick={ this.onClick }>
              Remove Access
            </Button>
          </Col>
        </Row>
      </div>
    );
  }
}
