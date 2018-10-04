import React, { Component } from 'react';
// components
import { TabPane } from 'reactstrap';
import { PlatoonRow } from '../rows';
import { Callout } from 'components/ui';
// import { Form } from 'components/inputs';
import { SaveButton } from 'components/buttons';
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
    const { platoon, tabId, updated, onSubmit, onDelete } = this.props;

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

        <form id='PlatoonTab' onSubmit={ onSubmit }>

          <PlatoonRow 
            platoon={ platoon } 
            onDelete={ onDelete } 
            inputProps={ inputProps }
            selectProps={ selectProps } />

          <SaveButton show={ updated } />

        </form>
      </TabPane>
    );
  }
}
