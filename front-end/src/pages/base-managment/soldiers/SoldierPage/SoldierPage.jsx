import React, { useState, useEffect, useCallback } from 'react';
import { connect } from 'react-redux';
import { useParams, Navigate } from 'react-router-dom';
// components

import { TabContent, Nav } from 'reactstrap';
import { LoadingScreen, FontAwesome } from 'components/ui';
import { NavigationTab, UnsavedChangesPrompt } from 'components/navigation';
import {
  PersonalTab, RankTab, MedalsTab,
  SettingsTab, RegistrationTab, TransactionsTab
} from './tabs';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { removeAuth, createAuth } from 'store/base/staff/operations';
import { showError } from 'functions/notifications';
import {
  getSoldier as getSoldierOp, updateSoldier as updateSoldierOp,
  deleteSoldier as deleteSoldierOp, updateMissions as updateMissionsOp, updateBirthday as updateBirthdayOp,
  uploadProfile as uploadProfileOp
} from 'store/base/soldiers/operations';
// styles
import './SoldierPage.scss';
import { isBC } from 'functions/login';

const SoldierPage = (props) => {
  const { login, getSoldier, deleteSoldier, updateBirthday, createAuth, removeAuth, updateSoldier, updateMissions } = props;
  const { id } = useParams();

  // state
  const [soldier, setSoldier] = useState({});
  const [updates, setUpdates] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [activeTab, setActiveTab] = useState(1);
  const [valid, setValid] = useState({ soldier: true, settings: true });

  const fetchSoldier = useCallback(() => {
    setLoading(true);
    getSoldier(id)
      .then(data => {
        setSoldier(data);
        setLoading(false);
      })
      .catch(error => {
        toast.error(error.message);
        setSoldier(undefined);
      });
  }, [getSoldier, id]);

  // load user on page load
  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    fetchSoldier();
  }, [fetchSoldier]);

  // set the title once we have the info
  useEffect(() => {
    if (soldier && soldier.user_serial) {
      setTitle(`Soldier #${soldier.user_serial}`);
    }
  }, [soldier]);

  const handleDeleteSoldier = (user_id) => {
    deleteSoldier(user_id)
      .then(msg => {
        toast.info(msg);
        fetchSoldier();
      })
      .catch(error => {
        toast.error(error.message);
      });
  };

  const handleUpdateBirthday = (user_id) => {
    updateBirthday(user_id)
      .then(msg => {
        toast.info(msg);
        fetchSoldier();
      })
      .catch(error => {
        toast.error(error.message);
      });
  };

  const handleCreateAuth = (auth) => {
    createAuth(auth).then(fetchSoldier);
  };

  const handleRemoveAuth = (auth) => {
    removeAuth(auth).then(fetchSoldier);
  };

  const toggleTab = (tab) => {
    setActiveTab(tab);
  };

  const onUpdate = (update) => {
    const newUpdates = filterUpdates(soldier, { ...updates, ...update });
    setUpdates(newUpdates);
  };

  const updateValidStatus = (tab) => (status) => {
    if (valid[tab] !== status) {
      setValid({ ...valid, [tab]: status });
    }
  };

  const updateProfilePicture = (formData) => {
    return showError(updateSoldier(soldier.user_id, formData)
      .then(update => {
        setUpdates({});
        setSoldier(update);
        return update;
      })
    );
  };

  const handleUpdateMissions = (missionUpdates) => {
    // update the missions
    return showError(updateMissions(soldier.user_id, missionUpdates)
      .then(({ errors, updated: count, medalBoard, rankBoard }) => {
        // update the soldier
        const newSoldier = { ...soldier, medalBoard, rankBoard };
        setSoldier(newSoldier);
        // show what was updated
        if (count > 0)
          toast.info(`${count} campaigns updated.`);
        // show all errors
        errors.forEach(e => toast.error(e));
      })
    );
  };

  const saveChanges = (event) => {
    event && event.preventDefault();

    if (saving) return true;

    // validate form
    const isInvalid = Object.values(valid).includes(false);
    if (isInvalid)
      return toast.error('Please correct all invalid fields');

    if (!soldier.class_id && updates.class_id === undefined) {
      return toast.error('You must choose a platoon.');
    }
    if (updates.school_id !== undefined && updates.class_id === undefined) {
      return toast.error('You must choose a platoon.');
    }
    if (updates.class_id !== undefined && updates.class_id === null) {
      return toast.error('You must choose a platoon.');
    }

    setSaving(true);
    // show errors from updating to the user
    showError(updateSoldier(soldier.user_id, updates)
      .then(updatedSoldier => {
        setUpdates({});
        setSoldier(updatedSoldier);
      })
    ).then(() => setSaving(false));
  };

  // if we do not have the soldier...
  if (soldier === undefined)
    return <Navigate to='/bm/soldiers' replace />;

  if (loading) return <LoadingScreen size='8' />;

  const currentSoldier = { ...soldier, ...updates };
  const isUpdated = Object.keys(updates).length > 0;

  const navProps = { onClick: toggleTab, activeTab };

  return (
    <div id='SoldierPage'>
      <UnsavedChangesPrompt
        when={isUpdated}
        message="You have unsaved changes. Are you sure you want to leave?"
      />
      <Nav tabs>

        <NavigationTab tab={1} icon='user' {...navProps}>
          {valid.soldier || <FontAwesome icon='exclamation' />} Soldier
        </NavigationTab>

        <NavigationTab tab={2} icon='sliders-h' {...navProps}>
          {valid.settings || <FontAwesome icon='exclamation' />} Settings
        </NavigationTab>

        <NavigationTab tab={3} icon='medal' {...navProps}>
          Rank
        </NavigationTab>

        {isBC(login.code) &&
          <NavigationTab tab={4} icon='award' {...navProps}>
            Medals
          </NavigationTab>
        }

        <NavigationTab tab={5} icon='registered' {...navProps}>
          Registration
        </NavigationTab>

        {/* { (isHQ ( login.code ) || [9, 255].includes(login.id))  &&  */
          <NavigationTab tab={6} icon='info-circle' {...navProps}>
            Transactions
          </NavigationTab>
        }

      </Nav>
      <TabContent activeTab={activeTab}>

        <PersonalTab
          tabId={1}
          login={login}
          saving={saving}
          soldier={currentSoldier}
          updated={isUpdated}
          onSubmit={saveChanges}
          handleChange={onUpdate}
          updateProfile={updateProfilePicture}
          onValidChange={updateValidStatus('soldier')} />

        <SettingsTab
          tabId={2}
          login={login}
          saving={saving}
          soldier={currentSoldier}
          updated={isUpdated}
          onSubmit={saveChanges}
          removeAuth={handleRemoveAuth}
          createAuth={handleCreateAuth}
          handleChange={onUpdate}
          deleteSoldier={handleDeleteSoldier}
          updateBirthday={handleUpdateBirthday}
          onValidChange={updateValidStatus('settings')} />

        <RankTab
          tabId={3}
          rank={currentSoldier.rank}
          miles={currentSoldier.miles}
          board={currentSoldier.rankBoard.ranks} />

        {isBC(login.code) &&
          <MedalsTab
            tabId={4}
            board={currentSoldier.medalBoard}
            updateMissions={handleUpdateMissions} />
        }

        <RegistrationTab
          tabId={5}
          soldier={currentSoldier} />

        {/* { (isHQ ( login.code ) || [9, 255].includes(login.id)) &&  */
          <TransactionsTab
            tabId={6}
            soldier={currentSoldier} />
        }

      </TabContent>
    </div>
  );
};

const mapStateToProps = (state) => {
  return {
    login: state.login.current_login
  };
}

const mapDispatchToProps = {
  removeAuth, createAuth, updateMissions: updateMissionsOp,
  getSoldier: getSoldierOp, updateSoldier: updateSoldierOp, deleteSoldier: deleteSoldierOp, updateBirthday: updateBirthdayOp,
  uploadProfile: uploadProfileOp
}

export default connect(mapStateToProps, mapDispatchToProps)(SoldierPage);
