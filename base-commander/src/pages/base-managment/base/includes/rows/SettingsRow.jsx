import React, { Component, Fragment } from 'react';
// components
import { Row, Col, Input } from 'reactstrap';
import { Radio, Checkbox, Date, Label } from 'components/inputs';
import { LEGACY_URL } from 'components/constants';
// functions
import julian from 'julian';
import moment from 'moment';
import { toJulian } from 'functions/dates';
import { onCheckboxChange, onInputChange, onNumberChange } from 'functions/events';

export class SettingsRow extends Component {

  state = {
    disabled: false,
    storeResetOptions: [],
    checked: false, // for radio buttons under store miles settings
  };

  componentDidMount() {
    fetch( `${LEGACY_URL}/api/registration/store_resets.php` )
      .then( res => res.json() )
      .then( resets => {
        console.log(resets.data)
        this.setState({ storeResetOptions: resets.data })
      })
  }

  onChange = onInputChange( this.props.onUpdate );
  handleCheckbox = onCheckboxChange( this.props.onUpdate );
  onNumberChange = onNumberChange( this.props.onUpdate );

  onDateChage = date =>
    this.props.onUpdate({ store_reset: date ? toJulian( date ) : date });

  disableSchoolReset = () => {
    const store_reset = this.props.base.store_reset > 0 ? 0 : toJulian( moment() );
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: true, checked: true });
  }

  enableSchoolReset = event => {
    const store_reset = event.target.value;
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: false, checked: true });
  }

  changeSchoolReset = event => {
    const store_reset = event.target.value;
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: true, checked: true });
  }

  enableCustom = event => {
    const store_reset = event.target.value;
    this.props.onUpdate({ store_reset });
    this.setState({ disabled: false, checked: false });
  }

  render () {
    const { base } = this.props;
    let {
      pic_mission_type,   store_reset,  school_gender,
      print_parent_tasks, allow_parent_tasks, rewards,
      chidon_posters_boys, chidon_posters_girls, one_time_prize_reset
    } = base;
    console.log("One time prize reset: " + one_time_prize_reset)

    const store_reset_jd = parseInt(store_reset, 10);
    store_reset = store_reset > 0 ? moment( julian.toDate( store_reset ) ) : undefined;
    //console.log( store_reset );
    //console.log( store_reset_jd );

    // props for all inputs
    const checkboxProps = { onChange: this.handleCheckbox };
    const schoolGenderProps = { name: 'school_gender', onChange: this.onChange }
    const missionTypeProps = { name: 'pic_mission_type', onChange: this.onNumberChange }
    const storeResetProps = { name: 'store_miles_reset', onChange: this.onChange }
    const oneTimeResetProps = { name: 'one_time_prize_reset', onChange: this.onChange }

    return (
      <Row id='SettingsRow'>
        <Col xs={12} sm={6} xl={4}>
          <Label>Base Gender</Label>
          <Radio value='M' 
              { ...schoolGenderProps }
              checked={ school_gender === 'M' } >
            Boys
          </Radio>

          <Radio value='F'
              { ...schoolGenderProps }
              checked={ school_gender === 'F' }>
            Girls
          </Radio>

          <Radio value='B'
              { ...schoolGenderProps }
              checked={ school_gender === 'B' }>
            Both
          </Radio>
        </Col>

        <Col xs={12} sm={6} xl={4}>
          <Label>Mission Sheet Type</Label>
          <Radio value='1' 
              { ...missionTypeProps }
              checked={ pic_mission_type === 1 } >
            No Pictures
          </Radio>

          <Radio value='2'
              { ...missionTypeProps }
              checked={ pic_mission_type === 2 }>
            Small Pictures
          </Radio>
        </Col>

        <Col xs={12} sm={6} xl={4}>
          <Label>Custom Parent Tasks</Label>
          <Checkbox checked={!!allow_parent_tasks} name='allow_parent_tasks' { ...checkboxProps} >
            Allow
          </Checkbox>
          <Checkbox checked={!!print_parent_tasks} name='print_parent_tasks' { ...checkboxProps} >
            Include on printable mission sheets
          </Checkbox>
        </Col>

        <Col xs={12} sm={6} xl={4}>
          <Label>Amount of Boys Chidon Posters</Label>
          <Input type='number'
              name='chidon_posters_boys'
              onChange={ this.onNumberChange }
              value={ chidon_posters_boys || 0 } />
        </Col>
        <Col xs={12} sm={6} xl={4}>
          <Label>Amount of Girls Chidon Posters</Label>
          <Input type='number'
              name='chidon_posters_girls'
              onChange={ this.onNumberChange }
              value={ chidon_posters_girls || 0 } />
        </Col>

      { rewards > 0 &&
      <Fragment>
        <Col sm={12} className='special-options'>
          <p className='title'>Store Miles Settings</p>
          <Label>Allow students to spend miles earned from:</Label>

          { this.state.storeResetOptions && this.state.storeResetOptions.map(reset => (
            <Fragment key={ reset.jd }>
              <Radio value={ reset.jd }
                name='store_miles_reset'
                { ...storeResetProps }
                onChange={ this.changeSchoolReset }
                checked={ store_reset_jd === parseInt(reset.jd) }>
                { reset.title } ({ reset.hDate } / { reset.date })
              </Radio>
              <br />
            </Fragment>
          ))}

          {/*<Radio value='2459757'*/}
          {/*  { ...storeResetProps }*/}
          {/*  onChange={ this.enableSchoolReset }*/}
          {/*  checked={ store_reset_jd === 2459757 }>*/}
          {/*  The beginning of the summer (Sunday 27 Sivan / June 26)*/}
          {/*</Radio>*/}
          {/*<br />*/}

          {/*<Radio value='2459822'*/}
          {/*  { ...storeResetProps }*/}
          {/*  onChange={ this.enableSchoolReset }*/}
          {/*  checked={ store_reset_jd === 2459822 }>*/}
          {/*  The beginning of the school year (Tuesday 3 Elul / August 30)*/}
          {/*</Radio>*/}
          {/*<br />*/}

          <Radio key={0} id='store_reset' value='0'
            name='store_miles_reset'
            { ...storeResetProps }
            onChange={ this.disableSchoolReset }
            checked={ store_reset_jd === 0 }>
            Always (This includes all miles from previous years)
          </Radio>
          <br />

          <Radio key={ toJulian( moment() ) } value={ toJulian( moment() ) }
            name='store_miles_reset'
            { ...storeResetProps }
            checked={ !this.state.checked }
            onChange={ this.enableCustom }>
            Custom Date:
          </Radio>
          <br />

          <Date value={ store_reset }
            disabled = { this.state.disabled }
            onChange={ this.onDateChage } />

          {/* <Date value={ store_reset }
            disabled = { !store_reset }
            onChange={ this.onDateChage } /> */}
          
          {/* <Radio name='store_miles_reset' checked={ !store_reset } id='store_reset' onChange={ this.disableSchoolReset }>
            Never (Points will continue accumulating from last year)
          </Radio> */}
            
          {/* <Checkbox checked={ !store_reset }
              id='store_reset' onChange={ this.disableSchoolReset }>
            Allow soldiers to spend all their miles.
          </Checkbox> */}

          {/* <UncontrolledTooltip placement="top" target="store_reset" autohide={ false }>
            This includes all miles from previous years
          </UncontrolledTooltip> */}
        </Col>

        <Col sm={12} className='special-options'>
          <p className='title'>One Time Prize Settings</p>
          <Label>Students should not be able to purchase more than one of the "one time prize" if purchased from:</Label>
          { this.state.storeResetOptions && this.state.storeResetOptions.map((reset, i) => (
            <Fragment key={ reset.jd }>
              <Radio value={ reset.jd }
                name='one_time_prize_reset'
                onChange={ this.onChange }
                required>
                { reset.title } ({ reset.hDate } / { reset.date })
              </Radio>
              <br />
            </Fragment>
          ))}
        </Col>
      </Fragment>
      }
      </Row>
    );
  }
}
