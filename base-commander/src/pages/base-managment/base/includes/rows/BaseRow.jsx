import React, { Component } from 'react';
// components
import { Row, Col, Input, UncontrolledTooltip } from 'reactstrap';
// functions
import { onInputChange, onSelectChange } from 'functions/events';
import InstitutionSelect from 'components/selects/redux/InstitutionSelect';

export class BaseRow extends Component {

  onChange = onInputChange( this.props.onUpdate );
  onSelectChange = onSelectChange( this.props.onUpdate );

  render () {
    let { 
      school_name, school_name_he, inst_id, hachayol_name, required 
    } = this.props;
    // props for all inputs
    const inputProps = { required, onChange: this.onChange };
    
    return (
      <Row>
        <Col xs={12} sm={6}>
          <label>Base Name (Public)</label>
          <Input { ...inputProps }  name='school_name'
            pattern='^.{3,255}$'    value={ school_name || '' }
            maxLength={ 255 }       title="3 to 255 letters" />

          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>

        <Col xs={12} sm={6} dir='rtl'>
          <label>Hebrew/Banner Name</label>
          <Input { ...inputProps }        name='school_name_he'
            maxLength={ 255 }             value={ school_name_he || '' }
            pattern='^[^a-zA-Z]{3,255}$'  title="Three or more Hebrew letters"/>

          <div className='invalid-message'>Please enter 3 or more <em>Hebrew</em> letters</div>
        </Col>

        <Col xs={12} sm={6}>
          <label id='inst-label'>Institution</label>

          <InstitutionSelect
            required
            value={ inst_id }
            onChange={ this.onSelectChange('inst_id') } />

          <UncontrolledTooltip placement="top" target="inst-label" autohide={ false }>
            Connect this base to an institution for them to access data associated with your base.
          </UncontrolledTooltip>
        </Col>

        <Col xs={12} sm={6}>
          <label>Hachayol Name</label>
          <Input { ...inputProps }  name='hachayol_name'
            pattern='^.{3,65}$'     value={ hachayol_name || '' }
            title="3 to 65 letters" maxLength={ 65 } />

          <div className='invalid-message'>Please enter 3 or more letters</div>
        </Col>
      </Row>
    );
  }
}
