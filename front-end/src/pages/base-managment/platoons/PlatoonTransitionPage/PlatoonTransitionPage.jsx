import React, { useState, useEffect, useCallback } from 'react';
import { connect } from 'react-redux';
// components
import { Callout } from 'components/ui';
import { Step1, Step2, Step3, Deploy } from './steps';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import {
  getUsers, changePlatoon, transitionPlatoons, removeSoldier
} from 'store/base/platoons/platoon_transition';
// styles
import './PlatoonTransitionPage.scss';

const PlatoonTransitionPage = ({ login }) => {

  const [from, setFrom] = useState({ school_id: false, class_id: false });
  const [to, setTo] = useState({ school_id: false, class_id: false });
  const [soldiers, setSoldiers] = useState([]);
  const [selection, setSelection] = useState([]);
  const [loading, setLoading] = useState(false);

  // set the state and get the first round of soldiers
  const setupPage = useCallback(() => {
    const { code, id } = login;
    if (code === 'BC') {
      setTo(prev => ({ ...prev, school_id: id }));
      setFrom(prev => ({ ...prev, school_id: id }));
      // getSoldiers() will be called via useEffect dependency or explicit call? 
      // class component had callback in setState.
      // We'll call getSoldiers immediately or rely on useEffect?
      // Actually, getSoldiers depends on `from` state.
      // So we should call getSoldiers inside the then block or useEffect.
      // Let's refactor getSoldiers to accept params or useEffect.
    }
  }, [login]);

  // get the soldiers we can transition
  // We need to use refs or pass state if we call it from setupPage before state updates?
  // No, let's just use `from` state.
  const handleGetSoldiers = () => {
    const fromState = from; // Capture current state or use ref if updated immediately above?
    // In functional, if we just updated `from`, `handleGetSoldiers` won't see it until next render.
    // So if setupPage updates `from` and calls `getSoldiers`.
    // We should use an effect to fetch soldiers when `from` changes? 
    // Or just fetch with the new values.

    // Actually, `setupPage` sets `to` and `from`. 
    // Then calls `getSoldiers`.
    // In useEffect, we can init page.
    // If we rely on useEffect for fetching when `from` changes, it might trigger too often.
    // Let's make getSoldiers accept `from` arg optionally?

    setLoading(true);
    setSelection([]);
    getUsers(from)
      .then(soldiers => {
        setSoldiers(soldiers);
        setLoading(false);
      })
      .catch(error => {
        setSoldiers([]);
        setLoading(false);
        toast.error(error.message);
      });
  }

  // To handle the initial load correctly:
  useEffect(() => {
    setTitle('Platoon Transition');
    const { code, id } = login;
    if (code === 'BC') {
      const initialFrom = { school_id: id, class_id: false };
      setFrom(initialFrom);
      setTo({ school_id: id, class_id: false });

      // Immediate fetch with initialFrom
      setLoading(true);
      setSelection([]);
      getUsers(initialFrom)
        .then(soldiers => {
          setSoldiers(soldiers);
          setLoading(false);
        })
        .catch(error => {
          setSoldiers([]);
          setLoading(false);
          toast.error(error.message);
        });
    }
  }, [login]); // Run once on mount (or when login changes)


  // update an id in `to` or `from`
  const selectChange = (section) => (id) => (option) => {
    const value = option && option.value;
    if (section === 'from') {
      setFrom(prev => ({ ...prev, [id]: value }));
    } else {
      setTo(prev => ({ ...prev, [id]: value }));
    }
  }

  const handleGetSoldiersClick = () => {
    // Wrapper for button click
    setLoading(true);
    setSelection([]);
    getUsers(from)
      .then(soldiers => {
        setSoldiers(soldiers);
        setLoading(false);
      })
      .catch(error => {
        setSoldiers([]);
        setLoading(false);
        toast.error(error.message);
      });
  }


  // Move to new base
  const move = () => {
    changePlatoon({ user_ids: selection, ...to })
      .then(handleGetSoldiersClick)
      .catch(error => toast.error(error.message));
  }

  // Discharge from Tzivos Hashem
  const discharge = () => {
    if (window.confirm('Soldier will be moved to "Unassigned School", Are you sure you want to do this?')) {
      removeSoldier(selection)
        // ( this.state.selection )
        .then(handleGetSoldiersClick)
        .catch(error => toast.error(error.message));
    }
  }

  // Deploy the transition
  const transition = () => {
    transitionPlatoons()
      .then(({ rowCount }) => toast.info(`${Math.floor(rowCount / 2)} Soldiers Transitioned`))
      .then(handleGetSoldiersClick)
      .catch(error => toast.error(error.message));
  }

  // Selection functions
  const updateSelection = selection => setSelection(selection);

  return (
    <div id='PlatoonTransitionPage'>
      <Callout title='Platoon Transition Instructions'>
        <p><strong>Platoon Transition allows you to setup a large scale transition for multiple soldiers in all bases your account has access to.</strong></p>
        <p>To setup this transition use steps 1-3 in order to move soldiers from one platoon to another.</p>
        <p><strong>Press the button below to deploy all your transitions whenever you like!</strong></p>
      </Callout>

      <Step1 {...from}
        login={login}
        loading={loading}
        onSubmit={handleGetSoldiersClick}
        selectChange={selectChange('from')} />

      <Step2
        soldiers={soldiers}
        selection={selection}
        updateSelection={updateSelection}
        loading={loading}
        discharge={discharge} />

      <Step3 {...to}
        selectChange={selectChange('to')}
        selection={selection}
        move={move}
        discharge={discharge} />

      <p className='title'>
        Step 4: Make all Transitions Live
      </p>
      <Deploy onDeploy={transition} />

    </div>
  );
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
})

export default connect(mapStateToProps)(PlatoonTransitionPage);
