import React, { Component } from 'react';
import { connect } from 'react-redux';
import api from 'api/api';
import { setTitle } from 'functions/utils';
import { Row, Col, Button } from 'reactstrap';
import { BaseSelect, PlatoonSelect, SoldierSelect, Select } from 'components/selects';
import { getSoldiers } from 'store/base/soldiers/operations';

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
    lang: 1
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
    .then((resp) => {
      if (resp && resp.success) {
        alert('Streak set up successfully.');
      } else {
        alert('Failed to set up streak.');
      }
    })
    .catch((err) => {
      alert('Failed to set up streak.');
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
        // Normalize to [{value, label}]
        let list = [];
        if (Array.isArray(resp)) {
          list = resp;
        } else if (resp && Array.isArray(resp.data)) {
          list = resp.data;
        } else if (resp && typeof resp === 'object') {
          list = Object.keys(resp).map(function(k){ return { value: k, label: resp[k] }; });
        }
        const options = (list || [])
          .map(function(it){
            if (it && typeof it === 'object') {
              var v = '';
              if (it.value !== undefined && it.value !== null && it.value !== '') v = it.value;
              else if (it.id !== undefined && it.id !== null && it.id !== '') v = it.id;
              else if (it.grid_id !== undefined && it.grid_id !== null && it.grid_id !== '') v = it.grid_id;
              var l = '';
              if (it.label !== undefined && it.label !== null && it.label !== '') l = it.label;
              else if (it.name !== undefined && it.name !== null && it.name !== '') l = it.name;
              else if (it.task_name !== undefined && it.task_name !== null && it.task_name !== '') l = it.task_name;
              else if (it.short_name !== undefined && it.short_name !== null && it.short_name !== '') l = it.short_name;
              else if (v !== '') l = String(v);
              return { value: String(v), label: String(l) };
            }
            return { value: String(it == null ? '' : it), label: String(it == null ? '' : it) };
          })
          .filter(function(o){ return o.value !== ''; })
          .sort(function(a,b){ return a.label.localeCompare(b.label); });
        this.setState(function(prev){
          const keep = options.some(function(o){ return String(o.value) === String(prev.form.task_id || ''); });
          const nextVal = keep ? prev.form.task_id : (options.length ? options[0].value : '');
          return { tasksOptions: options, form: Object.assign({}, prev.form, { task_id: nextVal }) };
        });
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

  render() {
    const form = this.state.form;
    return (
      <div id="StreaksPage">
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
            disabled={ !(this.state.form.user_id && this.state.form.subject_id && this.state.form.task_id) }
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