import React, { useState, useEffect, useCallback } from 'react';
import { connect } from 'react-redux';
import { useParams, Navigate } from 'react-router-dom';
// components

import { TabContent, Nav } from 'reactstrap';
import { NavigationTab, UnsavedChangesPrompt } from 'components/navigation';
import { FontAwesome, LoadingScreen } from 'components/ui';
// tabs
import { PlatoonTab, TeachersTab, SoldiersTab } from './tabs';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { getPlatoon as getPlatoonOp, updatePlatoon as updatePlatoonOp, deletePlatoon as deletePlatoonOp } from 'store/base/platoons/operations';
import { removeAuth, removeTeacher, createAuth } from 'store/base/staff/operations';
import { filterUpdates } from 'functions/events';

export const PlatoonPage = (props) => {
  const {
    getPlatoon, updatePlatoon, deletePlatoon,
    createAuth: createAuthOp, removeTeacher: removeTeacherOp
  } = props;
  const { id } = useParams();

  // state
  const [platoon, setPlatoon] = useState({});
  const [updates, setUpdates] = useState({});
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState(1);

  const fetchPlatoon = useCallback(() => {
    setLoading(true);
    getPlatoon(id)
      .then(data => {
        setPlatoon(data);
        setLoading(false);
      })
      .catch(error => {
        toast.error(error.message);
        setPlatoon(undefined);
      });
  }, [getPlatoon, id]);

  // load user on page load
  useEffect(() => {
    setTitle('Platoon');
    // eslint-disable-next-line react-hooks/set-state-in-effect
    fetchPlatoon();
  }, [fetchPlatoon]);

  // non-destructivly update the state
  const onUpdate = (update) => {
    const newUpdates = filterUpdates(platoon, { ...updates, ...update });
    setUpdates(newUpdates);
  };

  // toggle tabs
  const toggle = (tab) => () => setActiveTab(tab);

  // save platoon
  const save = (event) => {
    event && event.preventDefault();
    updatePlatoon(platoon.class_id, updates)
      .then(updatedPlatoon => {
        setPlatoon(updatedPlatoon);
        setUpdates({});
      });
  };

  const handleDelete = (event) => {
    event && event.preventDefault();
    if (!window.confirm('Are you sure you want to delete this platoon?'))
      return false;

    deletePlatoon(platoon.class_id)
      .then(() => { setPlatoon(undefined) })
      .catch(e => toast.error(e.message));
  };

  if (platoon === undefined)
    return <Navigate to='/bm/platoons' replace />;

  if (loading)
    return <LoadingScreen Callout />

  const isUpdated = Object.keys(updates).length > 0;
  const currentPlatoon = { ...platoon, ...updates };

  return (
    <div id='PlatoonPage'>
      <UnsavedChangesPrompt
        when={isUpdated}
        message="You have unsaved changes. Are you sure you want to leave?"
      />
      <Nav tabs>
        <NavigationTab active={activeTab === 1} onClick={toggle(1)}>
          Platoon <FontAwesome icon='chalkboard-teacher' />
        </NavigationTab>
        <NavigationTab active={activeTab === 2} onClick={toggle(2)}>
          Teacher Accounts <FontAwesome icon='user-lock' />
        </NavigationTab>
        {currentPlatoon.soldiers && currentPlatoon.soldiers.length > 0 &&
          <NavigationTab active={activeTab === 3} onClick={toggle(3)}>
            Soldiers <FontAwesome icon='users' />
          </NavigationTab>
        }
      </Nav>
      <TabContent activeTab={activeTab}>

        <PlatoonTab
          tabId={1}
          platoon={currentPlatoon}
          updated={isUpdated}
          onSubmit={save}
          onDelete={handleDelete}
          onUpdate={onUpdate} />

        <TeachersTab
          tabId={2}
          platoon={currentPlatoon}
          refresh={fetchPlatoon}
          createAuth={createAuthOp}
          removeAuth={removeTeacherOp} />

        <SoldiersTab
          tabId={3}
          users={currentPlatoon.soldiers} />

      </TabContent>

    </div>
  )
};

const mapStateToProps = ({ login }) => ({
  login: login.current_login,
})

const mapDispatchToProps = {
  getPlatoon: getPlatoonOp, updatePlatoon: updatePlatoonOp, deletePlatoon: deletePlatoonOp,
  removeAuth, removeTeacher, createAuth
}

export default connect(mapStateToProps, mapDispatchToProps)(PlatoonPage);
