import React, { useState } from 'react';

import { TabPane } from 'reactstrap';
import { UnsavedChangesPrompt } from 'components/navigation';
import { MedalBoard } from '../../components/MedalBoard';
import { SaveButton } from 'components/buttons/index';

export const MedalsTab = ({
  tabId,
  board,
  updateMissions
}) => {

  const [updates, setUpdates] = useState({});
  const [saving, setSaving] = useState(false);

  // subject_id => value
  const onMissionChange = (subject_id, value) => {
    // If value is undefined, remove it from updates
    if (value === undefined) {
      const newUpdates = { ...updates };
      delete newUpdates[subject_id];
      setUpdates(newUpdates);
      return;
    }

    setUpdates(prev => ({ ...prev, [subject_id]: value }));
  }

  const save = () => {
    // return false if we are already saving missions
    if (saving) return false;

    // map the updates to a array of objects
    const updatesArray = Object.entries(updates)
      .map(([id, val]) => ({
        subject_id: id,
        missions: parseInt(val, 10)
      }));

    // update the state
    setSaving(true);

    // update the mission and the state
    updateMissions(updatesArray)
      .then(() => {
        setUpdates({});
        setSaving(false);
      });
  }

  const update_count = Object.keys(updates).length;

  return (
    <TabPane id='MedalsTab' tabId={tabId}>

      <UnsavedChangesPrompt
        when={update_count > 0}
        message="You have unsaved medals changes. Are you sure you want to leave?"
      />

      <MedalBoard
        board={board}
        updates={updates}
        onMissionChange={onMissionChange} />

      <SaveButton show
        saving={saving}
        onClick={save}
        disabled={update_count <= 0 || saving}>

        Save updates to {update_count} Campaigns
      </SaveButton>

    </TabPane>
  )
}


