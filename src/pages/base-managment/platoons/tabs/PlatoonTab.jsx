import React, { Component } from 'react';
// components
import { PlatoonRow } from '../rows';
import { Link } from 'react-router-dom';
import { Callout } from 'components/ui';
import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Row, Col, Input, TabPane } from 'reactstrap';
// functions

export class PlatoonTab extends Component {
  
  // handle selects
  onChange = ({ target }) => {
    this.props.onUpdate({ [target.name]: target.value })
  };
  onSelectChange = ( option ) => {
    this.props.onUpdate({ [option.id]: option.value })
  };

  render(){
    const { platoon, tabId, updated, onSubmit, onValidChange } = this.props;

    const inputProps = { onChange: this.onChange };
    const selectProps = { onChange: this.onSelectChange };

    return (
      <TabPane tabId={ tabId }>

        <Callout title='Platoon Information'>
          <p>
            The teacher information below is the public facing information about this platoon.
            <strong> For example, the teacher name is used in the parent portal and the printed mission sheets. </strong>
          </p>
        </Callout>

        <Form id='PlatoonTab' onSubmit={ onSubmit } onValidChange={ onValidChange }>

          <PlatoonRow platoon={ platoon } 
            inputProps={ inputProps } selectProps={ selectProps } />

          <SaveButton show={ updated } />

        </Form>
      </TabPane>
    );
  }
}
