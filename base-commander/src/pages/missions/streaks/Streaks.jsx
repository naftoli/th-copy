import React, { Component } from 'react';
import { connect } from 'react-redux';
import api from 'api/api';
import { setTitle } from 'functions/utils';
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect, SoldierSelect, Select } from 'components/selects';
import { getSoldiers } from 'store/base/soldiers/operations';
import Callout from 'components/ui/Callout';
class Streaks extends Component {
  state = {
    form: {
      school_id: false,
      class_id: false,
      user_id: '',
      subject_id: '',
      task_id: ''
    },
    subjects: [],
    tasksOptions: [],
    yearStartJd: null,
    yearEndJd: null,
    lang: 1, 
    streaks: []
  };

  componentDidMount() {
    setTitle('Streaks');
    this.props.getSoldiers && this.props.getSoldiers();
    try {
      const params = new URLSearchParams(window.location.search);
      const uid = params.get('user_id') || params.get('id');
      const school = this.props.login && this.props.login.school_id;
      const klass = this.props.login && this.props.login.class_id;
      if (uid) {
        this.setState(function(prev){
          return { form: Object.assign({}, prev.form, { user_id: uid, school_id: school || prev.form.school_id, class_id: klass || prev.form.class_id }) };
        });
      } else if (school || klass) {
        this.setState(function(prev){
          return { form: Object.assign({}, prev.form, { school_id: school || prev.form.school_id, class_id: klass || prev.form.class_id }) };
        });
      }
    } catch (e) {}
  }

  handleSetupStreak = () => {
    const userId = this.state.form.user_id;
    const taskId = this.state.form.task_id;
    if (!userId || !taskId) {
      alert('Please select a Soldier and a Task to set up a streak.');
      return;
    }
    api.get('/missions/createStreak?' + new URLSearchParams({ gridId: taskId, userId: userId }).toString())
    .then((res) => {
        alert(res);
    })
    .catch((err) => {
        if (err.error && err.error.includes('Duplicate entry')) {
          alert('Streak already setup. Please choose a different task.');
          return;
        } 
        alert(err.error || 'Failed to setup streak');
        console.error(err);
    });
  }

  handleSelectChange = (key) => (option) => {
    const value = option ? option.value : false;
    this.setState(function(prev){
      const next = Object.assign({}, prev.form, { [key]: value });
      if (key === 'school_id') {
        next.user_id = false;
        next.class_id = false;
        next.subject_id = '';
        next.task_id = '';
      }
      return { form: next, error: null };
    }, () => {
      if (key === 'user_id') {
        if (this.state.form.user_id) {
          this.loadSubjectsForUser(this.state.form.user_id);
        } else {
          this.setState({ subjects: [], tasksOptions: [], form: Object.assign({}, this.state.form, { subject_id: '', task_id: '' }) });
        }
      }
      if (key === 'subject_id') {
        this.loadTasksForSubject();
      }
    });
  }

  handlePlatoonChange = (option) => {
    const value = option ? option.value : false;
    this.setState(function(prev){
      const next = Object.assign({}, prev.form, { class_id: value, user_id: false });
      return { form: next, error: null };
    });
  }

  loadSubjectsForUser = async (userId) => {
    try {
      const list = await api.get('/missions/subjects?user_id=' + String(userId));
      this.setState(function(prev){
        return { subjects: list || [], form: Object.assign({}, prev.form, { subject_id: '', task_id: '' }) };
      });
    } catch (e) {
      this.setState({ subjects: [] });
    }
  }

  loadTasksForSubject = () => {
    const subjectId = this.state.form.subject_id;
    const userId = this.state.form.user_id;
    if (!subjectId || !userId) {
      this.setState({ tasksOptions: [], form: Object.assign({}, this.state.form, { task_id: '' }) });
      return;
    }
    api.get('/missions/streak-tasks?subject_id=' + String(subjectId) + '&user_id=' + String(userId))
      .then((resp) => {
        console.log(resp);
        console.log(resp.tasks);
        // Normalize to [{value, label}]
        const tasks = Object.keys(resp.tasks).map(function(key){ return { value: key, label: resp.tasks[key] }; });
        this.setState({ tasksOptions: tasks, form: Object.assign({}, this.state.form, { task_id: tasks[0].value }) });
      })
      .catch(() => this.setState({ tasksOptions: [], form: Object.assign({}, this.state.form, { task_id: '' }) }));
  };

  getFilteredSoldiers() {
    const soldiers = this.props.soldiers || [];
    const schoolId = this.state.form.school_id;
    const classId = this.state.form.class_id || false;
    if (!schoolId) return [];
    let s = soldiers.filter(function(sol){ return sol.school_id === String(schoolId) && (!!sol.class_id); });
    if (classId) s = s.filter(function(sol){ return sol.class_id === String(classId); });
    return s;
  }

  checkStreakDisabled() {
    let disable = false;
    if (this.state.streaks.length > 0) {
        disable = this.state.streaks.some(function(streak){ return streak.days < 90; });
    }
    return !(this.state.form.user_id && this.state.form.subject_id && this.state.form.task_id) || disable;
  }

  render() {
    const form = this.state.form;
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
                value={ form.school_id }
                onChange={ this.handleSelectChange('school_id') } />
            </Col>
            <Col sm="4">
              <label>Platoon</label>
              <PlatoonSelect
                isClearable
                value={ form.class_id }
                schoolId={ form.school_id }
                openMenuOnFocus={ false }
                placeholder='All Platoons'
                onChange={ this.handlePlatoonChange } />
            </Col>
            <Col sm="4">
              <label>Soldier</label>
              <SoldierSelect
                key={(form.school_id || '0') + '-' + (form.class_id || '0')}
                isClearable
                registeredOnly
                showAllOption={ false }
                onlyReloadSoldiersIfNotLoaded
                value={ form.user_id }
                classId={ form.class_id }
                schoolId={ form.school_id }
                openMenuOnFocus={ false }
                placeholder='Select Soldier'
                onChange={ this.handleSelectChange('user_id') } />
            </Col>
          </Row>

          {/* No submit button needed; content loads automatically after selecting a Soldier */}
        </form>

        <div style={{ marginTop: 12 }}>
          <Row>
            <Col sm="6">
              <label>Campaign</label>
              {(() => {
                const opts = (this.state.subjects || []).map(function(s){
                  return { value: s.subject_id, label: s.subject_name };
                });
                const selected = opts.find((o) => String(o.value) === String(this.state.form.subject_id)) || null;
                return (
                  <Select
                    options={ opts }
                    value={ selected }
                    onChange={ this.handleSelectChange('subject_id') }
                    isDisabled={ !(this.state.subjects && this.state.subjects.length) }
                    placeholder={ this.state.subjects && this.state.subjects.length ? 'Choose campaign' : 'Select a Soldier first' }
                    isClearable={ false }
                  />
                );
              })()}
            </Col>
            <Col sm="6">
              <label>Tasks</label>
              {(() => {
                const opts = this.state.tasksOptions || [];
                const selected = opts.find((o) => String(o.value) === String(this.state.form.task_id)) || null;
                return (
                  <Select
                    options={ opts }
                    value={ selected }
                    onChange={ this.handleSelectChange('task_id') }
                    isDisabled={ !(this.state.form.subject_id && opts.length) }
                    placeholder={ this.state.form.subject_id ? (opts.length ? 'Select task' : 'No tasks') : 'Select a campaign first' }
                    isClearable={ false }
                  />
                );
              })()}
            </Col>
          </Row>
        </div>

        <div style={{ marginTop: 25 }}>
          <Button
            color="primary"
            disabled={ this.checkStreakDisabled() }
            onClick={ this.handleSetupStreak } 
          >
            Setup Streak
          </Button>
        </div>
      </div>
    );
  }
}

const mapStateToProps = ({ login, base }) => ({
  login: login.current_login,
  soldiers: base && base.soldiers && base.soldiers.soldiers
});

export default connect(mapStateToProps, { getSoldiers })(Streaks);