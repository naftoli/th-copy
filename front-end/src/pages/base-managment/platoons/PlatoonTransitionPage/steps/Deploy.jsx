import React from 'react';
// components
import { ButtonBar, FontAwesome } from 'components/ui';
import { Button, Row, Col } from 'reactstrap';

const Deploy = ({ onDeploy }) => {
  return (
    <Row id='deploy'>
      <Col>
        <ButtonBar>
          <Button color='primary' onClick={onDeploy}>
            <FontAwesome icon="rocket" />{' '}
            Deploy Transition (Make Changes Live)
          </Button>
        </ButtonBar>
      </Col>
    </Row>
  )
}

export default Deploy;
