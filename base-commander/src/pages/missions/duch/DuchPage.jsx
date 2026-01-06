import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout, FontAwesome } from 'components/ui';
import { Row, Col, Button } from 'reactstrap';
import {
  PlatoonSelect, SoldierSelect,
  Radio, BaseSelect
} from 'components/inputs';
import { Select } from 'components/selects';
// functions
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
// style
import '../print/PrintPage.scss';
import { LEGACY_URL } from 'components/constants';

class DuchPage extends Component {

  state = {
    school_id: false,
    class_ids: [],
    user_ids: [],
    double_sided: true, 
    dates: 'hebrew',
    start: '',
    end: '',
    date_range: '30'
  };

  componentDidMount() {
    setTitle('Duch');
    // set the default school id and class id
    const { school_id, class_id } = this.props.login;
    this.setState({ school_id });
    // set the default class id
    if (class_id) {
      this.setState({ class_ids: [ class_id ] });
    }
  }

  schoolChange = ({ value }) => {
    this.setState({ 
      school_id: value,
      class_ids: [],
      user_ids: [],
    });
  };
  handlePlatoonChange = (values) => {
    const class_ids = Array.isArray(values) ? values.map(v => v.value) : [];
    this.setState({ class_ids, user_ids: [] });
  };
  handleSoldierChange = (values) => {
    const user_ids = Array.isArray(values) ? values.map(v => v.value) : [];
    this.setState({ user_ids });
  };

  toggleDates = (e) => this.setState({ dates: e.target.value });
  toggleDoubleSided = (e) => this.setState({ double_sided: JSON.parse(e.target.value) });

  // basic input handler (start/end)
  handleChange = (e) => {
    const name = e.target.name;
    const value = e.target.value;
    this.setState({ [name]: value });
  }

  handleSelectChange = (key) => (option) => {
    const value = option ? option.value : '';
    this.setState({ [key]: value });
  }

  render() {
    const {
      school_id, class_ids, user_ids,
      double_sided, dates,
      start, end, date_range
    } = this.state;

    const { login } = this.props;

    return (
      <div id='DuchPage'>
        <Callout title='Duch'>
          <p><strong>Prepare and submit a Duch.</strong></p>
          <p>
            Select the parameters below and submit to generate the Duch.
          </p>
        </Callout>

        <form target='_blank' method='post' action={`${LEGACY_URL}/api/print/duch`}>
          <Row>
            <Col sm={4}>
              <label>Base</label>
              <BaseSelect
                name='school_id' onChange={this.schoolChange}
                value={school_id} isDisabled={!isAdmin(login.code)} />
              <input type='hidden' value={school_id} name='school_id' />
            </Col>

            <Col sm={4}>
              <label>Platoon(s)</label>
              <PlatoonSelect
                placeholder='All Platoons' isMulti values={class_ids}
                isDisabled={!isBC(login.code)} openMenuOnFocus={false}
                schoolId={school_id} onChange={this.handlePlatoonChange} />
              <input type='hidden' value={(class_ids || []).join(',')} name='class_ids' />
            </Col>

            <Col sm={4}>
              <label>Soldier(s)</label>
              <SoldierSelect
                values={user_ids} isMulti registeredOnly
                schoolId={school_id} classIds={class_ids} openMenuOnFocus={false}
                onChange={this.handleSoldierChange} placeholder='All Soldiers' />
              <input type='hidden' value={(user_ids || []).join(',')} name='user_ids' />
            </Col>
          </Row>

          <Row style={{ height: 10 }} />
          <Row>
            <Col sm={4}>
              <label>Start</label>
              <input type='date' name='start' value={start} onChange={this.handleChange} className='form-control' />
            </Col>
            <Col sm={4}>
              <label>End</label>
              <input type='date' name='end' value={end} onChange={this.handleChange} className='form-control' />
            </Col>
            <Col sm={4}>
              <label>Or last Number of days</label>
              {(() => {
                const lastNDaysOptions = [
                  { value: '7', label: '7' },
                  { value: '30', label: '30' },
                  { value: '60', label: '60' },
                  { value: '90', label: '90' },
                ];
                const selected = lastNDaysOptions.find(o => String(o.value) === String(date_range)) || null;
                return (
                  <Select
                    options={ lastNDaysOptions }
                    value={ selected }
                    onChange={ this.handleSelectChange('date_range') }
                  />
                );
              })()}
              <input type='hidden' name='date_range' value={date_range} />
            </Col>
          </Row>

          <Row>
            <Col sm={6} xl={3}>
              <label>Double sided</label><br />
              <Radio name='double_sided' checked={double_sided}
                value={true} onChange={this.toggleDoubleSided}>
                I <strong>am</strong> printing double sided copies.
              </Radio><br />
              <Radio name='double_sided' checked={!double_sided}
                value={false} onChange={this.toggleDoubleSided}>
                I am <strong>not</strong> printing double sided copies.
              </Radio>
            </Col>

            <Col sm={6} xl={3}>
              <label>Dates</label><br />
              <Radio name='dates' checked={dates === 'none'}
                value='none' onChange={this.toggleDates}>
                <strong>No</strong> dates.
              </Radio>
              <Radio name='dates' checked={dates === 'hebrew'}
                value='hebrew' onChange={this.toggleDates}>
                <strong>Hebrew</strong> dates.
              </Radio><br />
              <Radio name='dates' checked={dates === 'english'}
                value='english' onChange={this.toggleDates}>
                <strong>Hebrew and English</strong> dates.
              </Radio>
            </Col>
          </Row>

          <Row className='buttons' style={{ marginTop: '20px' }}>
            <Col sm={6}>
              <Button color='primary' >
                <FontAwesome icon='file' /> Generate Duch
              </Button>
            </Col>
          </Row>
        </form>
      </div>
    );
  }
}

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
};

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(DuchPage);


