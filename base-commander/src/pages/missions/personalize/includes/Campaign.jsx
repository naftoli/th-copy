import React, { Component } from 'react';

import classnames from 'classnames';

import Task from './Task';
import { Collapse, Alert } from 'reactstrap';
import { Toggle } from 'components/inputs/Toggle';
import { FontAwesome, Spinner } from 'components/ui';

class Campaign extends Component {

  state = {
    isOpen: false
  };

  toggle = e => {
    e.stopPropagation();
    if ( !this.props.tasks && !this.state.isOpen )
      this.props.getTasks( this.props.subject_id );
    // update the dropdown
    this.setState({ isOpen: !this.state.isOpen })
  };

  personalize = () => this.props.personalize({
    level: 'campaign',
    enrolled: !this.props.enrolled,
    subject_id: this.props.subject_id
  });

  render() {
    const {
      enrolled,   subject_name, tasks,
      subject_id, getMissions,  personalize
    } = this.props;

    const classNames = classnames({
      'Campaign': true,
      'open': this.state.isOpen
    })

    return (
      <div className={ classNames }>
        <span className='btn' onClick={ this.toggle }>
          <Toggle 
            className='small'
            checked={ enrolled } 
            onChange={ this.personalize } />

          <FontAwesome icon='caret-right'/> { subject_name }
        </span>

        <Collapse isOpen={ this.state.isOpen }>
          <div className='campaign-content'>
            { !tasks && <Spinner size={ 5 } /> }
          
            { tasks && tasks.length === 0 &&
              <Alert color='info'>No Tasks Available</Alert>
            }

            { tasks && tasks.length > 0 &&
              <div className='tasks'>
                { tasks.map( ( task, index ) => 
                  <Task 
                    { ...task }
                    key={ index }
                    disabled={ !enrolled }
                    subject_id={ subject_id }
                    getMissions={ getMissions }
                    personalize={ personalize } />
                )}
              </div>
            }
          </div>
        </Collapse>
      </div>
    );
  }
}

export default Campaign;