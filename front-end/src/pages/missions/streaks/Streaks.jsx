import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
import api from 'api/api';
import { setTitle } from 'functions/utils';
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect, SoldierSelect, Select } from 'components/selects';
import { getSoldiers } from 'store/base/soldiers/operations';
import Callout from 'components/ui/Callout';

const Streaks = ({ login, soldiers, getSoldiers }) => {
  const [form, setForm] = useState({
    school_id: false,
    class_id: false,
    user_id: '',
    subject_id: '',
    task_id: ''
  });
  const [subjects, setSubjects] = useState([]);
  const [tasksOptions, setTasksOptions] = useState([]);
  const [streaks, setStreaks] = useState([]);

  useEffect(() => {
    setTitle('Streaks');
    getSoldiers && getSoldiers();
    
    try {
      const params = new URLSearchParams(window.location.search);
      const uid = params.get('user_id') || params.get('id');
      const school = login?.school_id;
      const klass = login?.class_id;
      
      if (uid) {
        setForm(prev => ({
          ...prev,
          user_id: uid,
          school_id: school || prev.school_id,
          class_id: klass || prev.class_id
        }));
      } else if (school || klass) {
        setForm(prev => ({
          ...prev,
          school_id: school || prev.school_id,
          class_id: klass || prev.class_id
        }));
      }
    } catch (e) {
      console.error(e);
    }
  }, []);

  useEffect(() => {
    if (form.user_id) {
      loadSubjectsForUser(form.user_id);
    } else {
      setSubjects([]);
      setTasksOptions([]);
      setForm(prev => ({ ...prev, subject_id: '', task_id: '' }));
    }
  }, [form.user_id]);

  useEffect(() => {
    loadTasksForSubject();
  }, [form.subject_id]);

  const handleSetupStreak = () => {
    const userId = form.user_id;
    const taskId = form.task_id;
    
    if (!userId || !taskId) {
      alert('Please select a Soldier and a Task to set up a streak.');
      return;
    }
    
    api.get('/missions/createStreak?' + new URLSearchParams({ streakId: taskId, userId: userId }).toString())
      .then((res) => {
        alert(res);
        loadTasksForSubject();
      })
      .catch((err) => {
        if (err.error && err.error.includes('Duplicate entry')) {
          alert('Streak already setup. Please choose a different task.');
          return;
        }
        alert(err.error || 'Failed to setup streak');
        console.error(err);
      });
  };

  const handleSelectChange = (key) => (option) => {
    const value = option ? option.value : false;
    setForm(prev => {
      const next = { ...prev, [key]: value };
      if (key === 'school_id') {
        next.user_id = false;
        next.class_id = false;
        next.subject_id = '';
        next.task_id = '';
      }
      return next;
    });
    setStreaks([]);
  };

  const handlePlatoonChange = (option) => {
    const value = option ? option.value : false;
    setForm(prev => ({
      ...prev,
      class_id: value,
      user_id: false
    }));
  };

  const loadSubjectsForUser = async (userId) => {
    try {
      const list = await api.get('/missions/subjects?user_id=' + String(userId) + '&for_streak=1');
      setSubjects(list || []);
      setForm(prev => ({ ...prev, subject_id: '', task_id: '' }));
    } catch (e) {
      setSubjects([]);
    }
  };

  const loadTasksForSubject = () => {
    const subjectId = form.subject_id;
    const userId = form.user_id;
    
    if (!subjectId || !userId) {
      setTasksOptions([]);
      setForm(prev => ({ ...prev, task_id: '' }));
      return;
    }
    
    api.get('/missions/streak-tasks?subject_id=' + String(subjectId) + '&user_id=' + String(userId))
      .then((resp) => {
        const tasks = Object.keys(resp.tasks).map(key => ({ value: key, label: resp.tasks[key] }));
        const streaksById = resp.streaks || {};
        const streaksList = Object.keys(streaksById).map(streakId => {
          const s = streaksById[streakId] || {};
          return {
            streakId: streakId,
            name: s.name,
            cat: s.cat,
            days_done: s.days_done,
            days_needed: s.days_needed
          };
        });
        
        const hasIncomplete = streaksList.some(s => 
          (parseInt(s.days_done, 10) || 0) < (parseInt(s.days_needed, 10) || 0)
        );
        
        if (hasIncomplete) {
          alert('This soldier already has an active streak in progress. You cannot choose another streak until the current streak completes 90 days.');
        }
        
        setTasksOptions(tasks);
        setStreaks(streaksList);
        setForm(prev => ({ ...prev, task_id: tasks[0] ? tasks[0].value : '' }));
      })
      .catch(() => {
        setTasksOptions([]);
        setForm(prev => ({ ...prev, task_id: '' }));
      });
  };

  const checkStreakDisabled = () => {
    const hasIncomplete = streaks.some(s => 
      (parseInt(s.days_done, 10) || 0) < (parseInt(s.days_needed, 10) || 0)
    );
    return !(form.user_id && form.subject_id && form.task_id) || hasIncomplete;
  };

  return (
    <div id="StreaksPage">
      <Callout title='Streaks'>
        <p>Set up a streak for a soldier for a specific task.</p>
      </Callout>
      
      <form>
        <Row>
          <Col sm="4">
            <label>Base</label>
            <BaseSelect
              required
              value={form.school_id}
              onChange={handleSelectChange('school_id')} />
          </Col>
          <Col sm="4">
            <label>Platoon</label>
            <PlatoonSelect
              isClearable
              value={form.class_id}
              schoolId={form.school_id}
              openMenuOnFocus={false}
              placeholder='All Platoons'
              onChange={handlePlatoonChange} />
          </Col>
          <Col sm="4">
            <label>Soldier</label>
            <SoldierSelect
              key={(form.school_id || '0') + '-' + (form.class_id || '0')}
              isClearable
              registeredOnly
              showAllOption={false}
              onlyReloadSoldiersIfNotLoaded
              value={form.user_id}
              classId={form.class_id}
              schoolId={form.school_id}
              openMenuOnFocus={false}
              placeholder='Select Soldier'
              onChange={handleSelectChange('user_id')} />
          </Col>
        </Row>
      </form>

      <div style={{ marginTop: 12 }}>
        <Row>
          <Col sm="6">
            <label>Campaign</label>
            <Select
              options={subjects.map(s => ({ value: s.subject_id, label: s.subject_name }))}
              value={subjects.map(s => ({ value: s.subject_id, label: s.subject_name }))
                .find(o => String(o.value) === String(form.subject_id)) || null}
              onChange={handleSelectChange('subject_id')}
              isDisabled={!(subjects && subjects.length)}
              placeholder={subjects && subjects.length ? 'Choose campaign' : 'Select a Soldier first'}
              isClearable={false}
            />
          </Col>
          <Col sm="6">
            <label>Tasks</label>
            <Select
              options={tasksOptions}
              value={tasksOptions.find(o => String(o.value) === String(form.task_id)) || null}
              onChange={handleSelectChange('task_id')}
              isDisabled={!(form.subject_id && tasksOptions.length)}
              placeholder={form.subject_id ? (tasksOptions.length ? 'Select task' : 'No tasks') : 'Select a campaign first'}
              isClearable={false}
            />
          </Col>
        </Row>
      </div>

      <div style={{ marginTop: 25 }}>
        <Button
          color="primary"
          disabled={checkStreakDisabled()}
          onClick={handleSetupStreak}
        >
          Setup Streak
        </Button>
      </div>
      <hr />

      {(form.user_id && streaks && streaks.length > 0) && (
        <div style={{ marginTop: 20 }}>
          <h5>Active Streak</h5>
          {streaks.map((s, idx) => (
            <div key={String(s.streakId) + '-' + String(idx)} style={{ padding: '8px 0' }}>
              <div><b>{s.cat}</b> - {s.name || 'Task'} ({s.days_done} days)</div>
              <progress style={{ height: '30px' }} value={s.days_done} max={s.days_needed} />
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

const mapStateToProps = ({ login, base }) => ({
  login: login.current_login,
  soldiers: base?.soldiers?.soldiers
});

export default connect(mapStateToProps, { getSoldiers })(Streaks);
