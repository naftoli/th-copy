import React, { useState, useEffect } from 'react';
import classnames from 'classnames';

import Label from './Label';
import Mission from './Mission';
import { Collapse } from 'reactstrap';
import { Toggle } from 'components/inputs';
import { Spinner, FontAwesome } from 'components/ui';


const Task = ({
  task, enrolled, disabled,
  labels, missions, subject_id,
  personalize, mandatory, getMissions
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [isUpdating, setIsUpdating] = useState(false);
  const [enrolledState, setEnrolledState] = useState(enrolled);

  useEffect(() => {
    setEnrolledState(enrolled);
  }, [enrolled]);

  const toggle = () => {
    if (!missions && !isOpen)
      getMissions(subject_id, task);
    // update the dropdown
    setIsOpen(prev => !prev);
  };

  const personalizeFunc = () => {
    setIsUpdating(true);
    personalize({
      level: 'task',
      task: task,
      enrolled: !enrolled,
      subject_id: subject_id,
    })
      .then(() => {
        setIsUpdating(false);
      })
      .catch(error => {
        setIsUpdating(false);
        throw error;
      });
  }

  const classNames = classnames({
    'Task': true,
    'open': isOpen
  });

  const redStyle = { color: "red" }

  return (
    <div className={classNames}>

      <Toggle
        className='small'
        disabled={disabled || isUpdating}
        onChange={personalizeFunc}
        checked={disabled ? false : enrolled} />

      {isUpdating && <span style={{ float: "right" }}>Saving...</span>}

      <p className='task' onClick={toggle}>
        <FontAwesome icon='caret-right' /> {task}
        {mandatory === 1 && <span style={redStyle}> * </span>}
      </p>

      {labels.map((label, index) =>
        <Label key={index} {...label} />
      )}

      <Collapse isOpen={isOpen}>

        <hr />

        {!missions && <Spinner size={4} />}

        <div className='missions'>
          {missions && missions.map((mission, index) =>
            <Mission
              key={index}
              task={task}
              {...mission}
              subject_id={subject_id}
              personalize={personalize}
              disabled={!enrolled || disabled} />
          )}
        </div>

      </Collapse>
    </div>
  );
}

export default Task;
