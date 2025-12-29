import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import { Collapse } from 'reactstrap';
import { Callout } from 'components/ui';
import { Label } from 'components/missions';
import MissionsForm from './includes/MissionsForm';
// store and constants
import { setMissions } from 'store/missions/mark/actions';
import { markMission } from 'store/missions/mark/operations';

export const BCMarkPage = (props) => {
  const { loading, missions } = useSelector(({ missions }) => missions.mark);
  const dispatch = useDispatch();

  const [user_id, setUserId] = useState(false);

  useEffect(() => {
    return () => {
      dispatch(setMissions([]));
    }
  }, [dispatch]);

  const handleMarkMission = (mission, status) => dispatch(markMission(mission, status));

  const organizeByLabel = (accumulator, value) => {
    // if we have hit a new label that we have not seen before
    if (!accumulator[value.label_name])
      accumulator[value.label_name] = [];
    // add the mission to the label
    accumulator[value.label_name].push(value);
    return accumulator;
  }

  const organizedMissions = missions.reduce(organizeByLabel, {});
  const labels = Object.keys(organizedMissions);

  return (
    <div id='BCMarkPage'>
      <div className='no-print'>
        <Callout title='Mark Missions'>
          <p><strong>Load the</strong> missions to mark the soldiers missions inline in a mobile-frendly way.</p>
          <p>Press the <strong>Mark Printed Version</strong> button to use the old marking page and mark the sheets as printed.</p>
          <p>Enter a <strong>valid Serial Number</strong> (7xxxxxx) in the serial number box below to quick select the soldier.</p>
        </Callout>

        <MissionsForm
          user_id={user_id}
          onSoldierChange={setUserId} />
      </div>

      <Collapse isOpen={!loading} >
        <div id='missions'>
          {labels.map((label, index) =>
            <Label
              key={index}
              label={label}
              user_id={user_id}
              missions={organizedMissions[label]}
              markMission={handleMarkMission} />
          )}
        </div>
      </Collapse>
    </div>
  );
}

export default BCMarkPage;
