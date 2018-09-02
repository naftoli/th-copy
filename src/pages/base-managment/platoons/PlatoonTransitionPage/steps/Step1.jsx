import React, { Component } from 'react';
// components
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect } from 'components/inputs';
import { InlineSync } from 'components/ui';
import { isAdmin } from 'functions/login';

class Step1 extends Component {


  render() {
    const {
      school_id, class_id, selectChange, onSubmit, loading, login
    } = this.props;

    let baseSelect;
    if ( isAdmin( login.code ) )
      baseSelect = <BaseSelect value={ school_id } onChange={ selectChange( 'school_id' ) } fetchAll />;

    return (
      <div id='step-1'>
        <p className="title">Step 1: Select Platoon</p>
        <Row>
          { baseSelect &&
            <Col sm={4}>
              <label>From Base</label>
              { baseSelect }
            </Col>
          }
          <Col sm={4}>
            <label>From Platoon</label>
            <PlatoonSelect value={ class_id } schoolId={ school_id }
              onChange={ selectChange( 'class_id' ) } showNoneOption />
          </Col>
          <Col sm={4}>
            <Button color='primary' onClick={ onSubmit }>
              <InlineSync loading={loading} /> Load Soldiers
            </Button>
          </Col>
        </Row>
      </div>
    );
  }
};

export default Step1;
