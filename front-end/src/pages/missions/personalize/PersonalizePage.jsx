import React, { useState, useEffect, useRef } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// components
import Campaign from './includes/Campaign';
import { Row, Col, Button } from 'reactstrap';
import OptionsRow from './includes/OptionsRow';
import { Spinner, Callout, InlineSync } from 'components/ui';

// functions
import { setTitle } from 'functions/utils';
import {
  getCampaigns, getTasks,
  getMissions, personalize
} from 'store/missions/personalize/operations';
// style
import './PersonalizePage.scss';
import { showError } from 'functions/notifications';

export const PersonalizePage = () => {
  const dispatch = useDispatch();
  const { login, campaigns } = useSelector(({ login, missions }) => ({
    login: login.current_login,
    campaigns: missions.personalize
  }));

  const [state, setState] = useState({
    school_id: false, class_ids: [], user_id: false,
    parsha_id: false, mission_type: false, lang: '1',
    // keep track of what is loading and what the selected options are
    loading: false, current_options: {},
  });

  const { loading } = state;
  const initializedLoginDefaults = useRef(false);

  useEffect(() => {
    if (initializedLoginDefaults.current) return;
    initializedLoginDefaults.current = true;
    setTitle('Personalize Missions');
    // set the default school id and class id
    const { school_id, class_id } = login;
    setState(prev => ({
      ...prev,
      school_id,
      class_ids: class_id ? [class_id] : []
    }));
  }, [login]);

  /**
   * event handler for dropdown changes
   */
  const onSelectChange = key => option =>
    setState(prev => ({ ...prev, [key]: option ? option.value : false }));

  const onMultiSelectChange = key => options =>
    setState(prev => ({ ...prev, [key]: options && options.map ? options.map(o => o.value) : [] }));

  const onLangChange = ({ target }) =>
    setState(prev => ({ ...prev, lang: target.value || '1' }));

  const handleGetCampaigns = () => {
    // Destructure state to get current values to submit
    const data = { ...state };
    delete data.loading;
    delete data.current_options;

    setState(prev => ({ ...prev, current_options: data, loading: true }));

    showError(dispatch(getCampaigns(data)))
      .then(() => setState(prev => ({ ...prev, loading: false })));
  }

  const handleGetTasks = subject_id => showError(
    dispatch(getTasks(subject_id, state.current_options))
  );

  const handlePersonalize = updates => showError(
    dispatch(personalize(updates, state.current_options))
  );

  const handleGetMissions = (subject_id, task) => showError(
    dispatch(getMissions(subject_id, task, state.current_options))
  );

  return (
    <div id='PersonalizePage'>
      <Callout title='Personalize Missions'>
        <p>Platoon or Base wide settings will override any individial soldier's or platoon's settings.</p>
      </Callout>

      <OptionsRow
        login={login}
        {...state}
        onLangChange={onLangChange}
        onSelectChange={onSelectChange}
        onMultiSelectChange={onMultiSelectChange} />

      <Row className='buttons'>
        <Col sm={{ size: 6, offset: 3 }}>
          <Button color='primary' onClick={handleGetCampaigns}>
            <InlineSync loading={loading} /> Load Campaigns
          </Button>
        </Col>
      </Row>

      <hr />

      {loading && <Spinner />}

      <div className='campaigns'>
        {!loading && campaigns.map((campaign, index) =>
          <Campaign
            key={index}
            {...campaign}
            getTasks={handleGetTasks}
            personalize={handlePersonalize}
            getMissions={handleGetMissions} />
        )}
      </div>
    </div>
  );
}

export default PersonalizePage;
