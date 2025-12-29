import React, { useState } from 'react';

import classnames from 'classnames';

import Task from './Task';
import { Collapse, Alert } from 'reactstrap';
import { Toggle } from 'components/inputs/Toggle';
import { FontAwesome, Spinner } from 'components/ui';

const Campaign = ({
  enrolled, subject_name, tasks,
  subject_id, getMissions, personalize,
  getTasks
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isUpdating, setIsUpdating] = useState(false);

  const toggle = e => {
    e.stopPropagation();
    if (!tasks && !isOpen)
      getTasks(subject_id);
    // update the dropdown
    setIsOpen(prev => !prev);
  };

  const personalizeFunc = () => {
    setIsUpdating(true);
    personalize({
      level: 'campaign',
      enrolled: !enrolled,
      subject_id: subject_id
    })
      .then(() => {
        setIsUpdating(false);
      })
      .catch(error => {
        setIsUpdating(false);
        throw error;
      });
  };

  const classNames = classnames({
    'Campaign': true,
    'open': isOpen
  })

  return (
    <div className={classNames}>

      <span className='btn' onClick={toggle}>
        <Toggle
          className='small'
          checked={enrolled}
          disabled={isUpdating}
          onChange={personalizeFunc} />

        {isUpdating && <span style={{ float: "right" }}>Saving...</span>}

        <FontAwesome icon='caret-right' /> {subject_name}
      </span>

      <Collapse isOpen={isOpen}>
        <div className='campaign-content'>
          {!tasks && <Spinner size={5} />}

          {tasks && tasks.length === 0 &&
            <Alert color='info'>No Tasks Available</Alert>
          }

          {tasks && tasks.length > 0 &&
            <div className='tasks'>
              {tasks.map((task, index) =>
                <Task
                  {...task}
                  key={index}
                  disabled={!enrolled}
                  subject_id={subject_id}
                  getMissions={getMissions}
                  personalize={personalize} />
              )}
            </div>
          }
        </div>
      </Collapse>
    </div>
  );
}

export default Campaign;