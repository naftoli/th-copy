import React from 'react';
import classnames from 'classnames';
import { Checkbox } from 'components/inputs';
import { DateDisplay } from 'components/ui/Formats';

const Mission = ({
  name, enrolled, disabled, end,
  start, start_date, end_date, mandatory,
  task, subject_id, personalize
}) => {

  const personalizeFunc = () => personalize({
    level: 'mission',
    task: task,
    mission: name,
    enrolled: !enrolled,
    subject_id: subject_id,
  });

  const classNames = classnames({
    'Mission': true,
  });

  const mandStyle = { color: 'red' }

  return (
    <div className={classNames}>
      <Checkbox
        disabled={disabled}
        onChange={personalizeFunc}
        checked={disabled ? false : enrolled}>
        {name}
        {mandatory &&
          <span style={mandStyle}>*</span>
        }
        <small>{start} - {end}</small>
        <small><DateDisplay value={start_date} /> - <DateDisplay value={end_date} /></small>
      </Checkbox>
    </div>
  );
}

export default Mission;
