import React, { Component } from 'react';
// components
import { Row, Col, Button, Card } from 'reactstrap';
import { BaseLogo, FontAwesome } from 'components/ui';

export class Account extends Component {

  onClick = () => {
    this.props.disconnect( this.props.type, this.props.id );
  }

  render() {
    const { name, img } = this.props;
    return (
      <Card className='Account'>
        <Row>
          <Col xs={3} xl={2}>
            <BaseLogo src={ img } />
          </Col>
          <Col xs={5} xl={6}>
            <span className='name'>{ name }</span>
          </Col>
          <Col xs={4}>
            <Button color='danger' onClick={ this.onClick }>
              <FontAwesome icon='trash'/> Remove
            </Button>
          </Col>
        </Row>
      </Card>
    );
  }
}
