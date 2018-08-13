import React, { Component } from 'react';
// components
import { BaseSelect } from 'components/selects';
import { Button, ButtonGroup, Row, Col } from 'reactstrap';


class Deploy extends Component {
  
  state = { school_id: false }

  selectChange = ( option ) => {
    this.setState({ school_id: option && option.value });
  }

  transition = () => {
    this.props.onDeploy( this.state.school_id );
  }
  
  render() {
    const { school_id } = this.state;
    return (
      <Row id='deploy'>
        <Col sm={6}>
          <BaseSelect value={ school_id } fetchAll onChange={ this.selectChange } />
        </Col>
        <Col sm={6}>
          <ButtonGroup>
            <Button color='primary' onClick={ this.transition }>
              <i className="fas fa-rocket"></i>{' '}
              Deploy Transition (Make Changes Live)
            </Button>
          </ButtonGroup>
        </Col>
      </Row>
    )
  }
}

export default Deploy;
