import React, { Component } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
// functions
import { eventToUpdate } from 'functions/events';

export class BaseRow extends Component {

  onChange = ({ target }) => {
    this.props.onUpdate( eventToUpdate( target, 'name' ) );
  }

  render () {
    let { 
      school_name, school_name_he, nickname, hachayol_name, required 
    } = this.props;
    // props for all inputs
    const inputProps = { required, onChange: this.onChange };
    
    
    return (
      <Row>
        {/* Base Name */}
        <Col xs={12} sm={6}>
          <label>Base Name</label>
          <Input name='school_name' value={ school_name } { ...inputProps } 
            pattern='^.{3,255}$' title="3 to 255 letters" maxLength={ 255 } />
          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>
        <Col xs={12} sm={6} dir='rtl'>
          <label>Hebrew Base Name</label>
          <Input name='school_name_he' value={ school_name_he } { ...inputProps }
            pattern='^[^a-zA-Z]{3,255}$' title="3 or more Hebrew letters" maxLength={ 255 } />
          <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
          <p className='input-message'>(This is how it will appear on school banner)</p>
        </Col>
        <Col xs={12} sm={6}>
          <label>Nickname</label>
          <Input name='nickname' value={ nickname || '' } { ...inputProps } 
            pattern='^.{3,155}$' title="3 to 155 letters" maxLength={ 155 } />
          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>
        <Col xs={12} sm={6}>
          <label>Hachayol Name</label>
          <Input name='hachayol_name' value={ hachayol_name || '' } { ...inputProps } 
            pattern='^.{3,65}$' title="3 to 65 letters" maxLength={ 65 } />
          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>
      </Row>
    );
  }
}
