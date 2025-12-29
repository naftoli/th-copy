import React, { useState, useEffect, useCallback } from 'react';
import PropTypes from 'prop-types';

import { Link } from 'react-router-dom';
import { FontAwesome } from 'components/ui';
import { Checkbox } from 'components/inputs';
import { Table, Input, Alert } from 'reactstrap';

import './styles/Grid.scss';

const GridCell = ({ mission, soldier, value: valueProp, onChange }) => {
  const [debounceTimer, setDebounceTimer] = useState(null);

  const handleChange = useCallback((e) => {
    let value = e.target.checked ? '1' : '0';

    if (e.target.type === 'number') {
      value = e.target.value;
      // Clear any existing timer
      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }
      // Debounce the save to prevent excessive API calls
      const timer = setTimeout(() => {
        onChange(value, mission, soldier);
      }, 300); // 300ms delay - much shorter than 1 second
      setDebounceTimer(timer);
    } else {
      onChange(value, mission, soldier);
    }
  }, [onChange, mission, soldier, debounceTimer]);


  const onBlur = (e) => {
    // Clear any pending debounced save
    if (debounceTimer) {
      clearTimeout(debounceTimer);
      setDebounceTimer(null);
    }
    // Save immediately on blur for number inputs
    if (e.target.type === 'number') {
      const value = e.target.value;
      onChange(value, mission, soldier);
    }
  }

  useEffect(() => {
    return () => {
      // Clean up timer if component unmounts
      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }
    };
  }, [debounceTimer]);

  let value = valueProp;

  // if it is a quantity based mission
  if (mission.quantity) {
    // set the value of the input
    if (!value && soldier && !!soldier.marks[mission.grid_id])
      value = soldier.marks[mission.grid_id].done_qty;
    // hide error
    if (!value && soldier)
      value = value || '';
    // return a input component
    return <Input
      min='0'
      type='number'
      value={value}
      onChange={handleChange}
      onBlur={onBlur}
    />;
  }

  if (!value && soldier)
    value = !!soldier.marks[mission.grid_id];
  // return a checkbox for on/off based tasks
  return <Checkbox
    checked={value}
    disabled={!!mission.disable}
    onChange={handleChange} />
}

export const Grid = ({ date, type, soldiers, missions, markTask }) => {

  // hide disabled missions from the GUI
  const filteredMissions = missions.filter(mission => !mission.disabled);

  const checkAll = (value, mission) => {
    const user_ids = soldiers
      .filter(soldier => // filter to soldiers that either
        !soldier.marks[mission.grid_id] || // 1. do not have a mark for this grid_id
        soldier.marks[mission.grid_id].done_qty !== value // 2. do have a mark that does not match the one entered at the top
      ).map(soldier => soldier.user_id);
    // mark the task
    markTask(
      type, user_ids, mission.grid_id,
      mission.mark_date || date, value
    );
  }

  const toggleTask = (value, mission, soldier) => {
    const user_ids = [soldier.user_id];
    markTask(
      type, user_ids, mission.grid_id,
      mission.mark_date || date, value
    );
  }


  if (filteredMissions.length === 0)
    return <Alert color="danger">No Available Tasks</Alert>

  return (
    <Table className='Grid' bordered striped hover responsive size="sm">
      <thead>
        <tr className='Grid-row header'>
          <th>
            <span>Missions <FontAwesome icon='arrow-right' /></span>
            <hr />
            <span>Soldiers <FontAwesome icon='arrow-down' /></span>
          </th>
          {filteredMissions.map((mission, index) =>
            <th key={index}>
              <span>{mission.cat} </span>
              {mission.mandatory_qty >= 1 && <FontAwesome icon='star' />}
            </th>
          )}
        </tr>
      </thead>

      <tbody>
        <tr className='Grid-row'>
          <td>
            <strong>All Soldiers</strong>
          </td>
          {filteredMissions.map((mission, index) =>
            <td key={index} >
              <GridCell
                mission={mission}
                onChange={checkAll} />
            </td>
          )}
        </tr>

        {soldiers.map((soldier, index) =>
          <tr className='Grid-row' key={index}>
            <td>
              <small>{soldier.rank.name}</small><br />
              <Link to={`/bm/soldiers/${soldier.user_id}`}>{soldier.name}</Link>
            </td>
            {filteredMissions.map((mission, index) =>
              <td key={index} >
                <GridCell
                  soldier={soldier}
                  mission={mission}
                  onChange={toggleTask} />
              </td>
            )}
          </tr>
        )}
      </tbody>
    </Table>
  )
}
