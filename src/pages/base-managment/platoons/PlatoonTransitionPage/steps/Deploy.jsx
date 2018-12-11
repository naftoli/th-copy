import React, { Component } from 'react';
// components
import { ButtonBar, FontAwesome } from 'components/ui';
import { Button, Row, Col } from 'reactstrap';

class Deploy extends Component {
  render() {
    return (
      <Row id='deploy'>
        <Col>
          <ButtonBar>
            <Button color='primary' onClick={ this.props.onDeploy }>
              <FontAwesome icon="rocket" />{' '}
              Deploy Transition (Make Changes Live)
            </Button>
          </ButtonBar>
        </Col>
      </Row>
    )
  }
}

export default Deploy;
