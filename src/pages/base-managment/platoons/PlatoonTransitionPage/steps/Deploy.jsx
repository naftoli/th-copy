import React, { Component } from 'react';
// components
import { BaseSelect } from 'components/inputs';
import { FontAwesome } from 'components/ui';
import { Button, ButtonGroup, Row, Col } from 'reactstrap';


class Deploy extends Component {
  render() {
    return (
      <Row id='deploy'>
        <Col>
          <ButtonGroup>
            <Button color='primary' onClick={ this.props.onDeploy }>
              <FontAwesome icon="rocket" />{' '}
              Deploy Transition (Make Changes Live)
            </Button>
          </ButtonGroup>
        </Col>
      </Row>
    )
  }
}

export default Deploy;
