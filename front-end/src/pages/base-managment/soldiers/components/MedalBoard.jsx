import React from 'react';

import Slider from 'rc-slider';
import { Button } from 'reactstrap';
import { LEGACY_URL } from 'components/constants';
import { FontAwesome } from 'components/ui';

export const MedalBoard = ({
  board = [],
  updates = {},
  onMissionChange = () => { }
}) => {
  return (
    <div className='MedalBoard'>
      {Array.isArray(board) &&
        board.map((medal, index) =>
          <Medal
            {...medal}
            key={index}
            onChange={onMissionChange}
            value={updates[medal.subject_id]} />
        )
      }
    </div>
  );
}

const Medal = ({
  medals,
  earned,
  subject_id,
  subject_name,
  onChange,
  value
}) => {

  const handleChange = val => {
    const max = medals[medals.length - 1].missions;

    // reset unchanged inputs
    if (val === earned)
      return reset();

    // prevent from going over the max
    let newValue = val;
    if (newValue > max)
      newValue = max;

    // and update accordingly
    return onChange(subject_id, newValue);
  }

  // handle slider changes
  const onSliderChange = val =>
    handleChange(val && val.toString());

  // handle the input event
  const onInputChange = ({ target }) =>
    handleChange(target.value);

  // get all earned medals and pop off the last one
  const getMedal = val =>
    medals.filter(medal => medal.missions <= val).pop();

  // reset the updates
  const reset = () =>
    onChange(subject_id, undefined);

  // go to the next medal
  const next = () => {
    let earnedVal = parseInt(earned, 10);
    const currentValue = value || earnedVal;

    const nextValue = medals.find(medal => medal.missions > currentValue);
    // If nextValue exists use its missions count, else unchanged?
    // Original: const nextValue = medals.find( medal => medal.missions > value ).missions;
    // but strict check on undefined vs logic.
    // Logic: find first medal with missions > current value.
    if (nextValue) {
      handleChange(nextValue.missions.toString());
    }
  }

  // Render logic
  // * clean the set the value if it is undefined
  const displayValue = value === undefined ? earned : value;

  let intval = parseInt(displayValue || 0, 10);
  if (intval < 0)
    intval = 0;

  // * get the current medal and the max value
  let currentMedal = getMedal(intval);
  const max = medals[medals.length - 1].missions;

  // reduce the marks into dots on the slider
  const marks = medals.reduce((acc, medal) => {
    return { ...acc, [medal.missions]: false }
  }, {});

  // make sure medal is never undefined
  if (!currentMedal)
    currentMedal = medals[0];

  return (
    <div className='Medal'>
      <div className='actions'>

        <Button outline
          color='primary'
          onClick={reset}
          disabled={displayValue === earned}>

          <FontAwesome icon='undo-alt' />
          <span>Reset Changes</span>
        </Button>

        <img
          alt={subject_name}
          className='medal-img'
          src={`${LEGACY_URL}${currentMedal.picture}`} />

        <Button outline
          color='primary'
          onClick={next}
          disabled={intval >= max}>

          <FontAwesome icon='arrow-right' />
          <span>Next Medal</span>
        </Button>

      </div>

      <strong>{subject_name}</strong>

      <div className='rc-slider-box'>
        <Slider
          min={0}
          max={max}
          value={intval}
          marks={marks}
          onChange={onSliderChange} />
      </div>

      <span>
        <input
          min={0}
          max={max}
          type='number'
          value={displayValue}
          placeholder={intval}
          onChange={onInputChange} />
        / {max} Missions - {currentMedal.color} Medal
      </span>
    </div>
  )
}
