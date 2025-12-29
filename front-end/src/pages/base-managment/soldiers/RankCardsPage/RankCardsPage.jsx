import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import RankCard from './RankCard';
import { ButtonBar, Callout, Spinner, FontAwesome } from 'components/ui';
import { Row, Col, Button, Alert, Input } from 'reactstrap';
import {
  PlatoonSelect, BaseSelect, Select,
  Checkbox, Radio, HeDate
} from 'components/inputs'
// functions
import is from 'is_js';
// import julian from 'julian';
import moment from 'moment';
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { findOption } from 'functions/selects';
import { setTitle } from 'functions/utils';
import { toJulian } from 'functions/dates';
// state
import { getRankCards, markPrinted } from 'store/base/soldiers/id_cards/operations';
// styles
import './RankCardsPage.scss';
import { onCheckboxChange, onJSONChange, onSelectChange, onInputChange, onJulianDateChange } from 'functions/events';

export const RankCardsPage = ({ login }) => {
  // initial state with default options
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [options, setOptions] = useState({
    school_id: false, class_id: false,
    rank: 1, user_serial: '', permanent: true,
    hide_printed: true, current: false,
    earned_before: toJulian(moment()),
    earned_start: 0,
    earned_end: 0
  });
  const [cards, setCards] = useState([]);
  const [updates, setUpdates] = useState([]);

  // change one or more options in the state
  const changeOption = changes => {
    setOptions(prev => ({ ...prev, ...changes }));
  }

  // standard input event handler
  const handleInputChange = onInputChange(changeOption);
  // Select returns an option, capture the key we are updating first
  const handleSelectChange = onSelectChange(changeOption);
  // cast the radio buttons value to JSON
  const handleRadioChange = onJSONChange(changeOption);
  // get the state of the checkbox
  const handleCheckboxChange = onCheckboxChange(changeOption, false);
  // convert the date selected to a julian date
  const handleDateChange = onJulianDateChange(changeOption);

  // on page load get all the users
  useEffect(() => {
    setTitle('Soldier Rank Cards');
    const { code, id } = login;
    if (code === 'BC')
      changeOption({ permanent: false, current: true, rank: false, school_id: id });
  }, []);

  // get the rank cards
  const handleGetRankCards = () => {
    setLoading(true);
    setUpdates([]);
    setCards([]);
    setLoaded(false);

    getRankCards(options)
      .then(fetchedCards => {
        setCards(fetchedCards);
        setLoading(false);
        setLoaded(true);
      })
      .catch(error => { toast.error(error.message) });
  }

  // handle marking that an item was printed
  const handlePrintedChange = (user_id, rank_ord, printed) => {
    let newUpdates = [...updates];
    // check if we already updated this one
    const updatedIndex = updates.findIndex(
      update => (update.user_id === user_id && update.rank_ord === rank_ord)
    );
    if (updatedIndex >= 0) {
      newUpdates = [...newUpdates.slice(0, updatedIndex), ...newUpdates.slice(updatedIndex + 1)];
    } else {
      newUpdates.push({ user_id, rank_ord, printed });
    }
    setUpdates(newUpdates);

    // update the cards and set the state
    const newCards = cards.map(
      card => card.user_id === user_id && card.rank_ord === rank_ord ? Object.assign({}, card, { printed }) : card
    );
    setCards(newCards);
  }

  // TODO, Extract and fetch data from server ( add ranks to redux state when needed to prevent re-fetching )
  const getRankOptions = () => {
    const ranks = [
      'All Ranks', 'Private', 'Sergeant', 'Sergeant Major', 'Second Lieutenant', 'First Lieutenant', 'Captain',
      'Major', 'Colonel', 'General', '1* General', '2* General', '3* General', '4* General', '5* General'
    ];
    return ranks.map((rank, index) => ({ value: index > 0 ? index : false, label: rank }));
  }

  const markPrintedFunc = (data) => {
    markPrinted(data)
      .then(response => {
        const newCards = cards.map(card => {
          const printed = response[card.user_id];
          return printed !== undefined ? Object.assign({}, card, { printed }) : card;
        });
        setUpdates([]);
        setCards(newCards);
      });
  }

  const handleMarkAllPrinted = printed => () => {
    const data = cards.map(({ user_id, rank_ord }) => ({ user_id, rank_ord, printed }));
    markPrintedFunc(data);
  }

  const syncPrinted = () => { markPrintedFunc(updates); }

  const print = () => { window.print(); }

  const JStoJulian = (dateStr) => {
    const date = new Date(dateStr);
    console.log(date)
    return Math.floor((date / 86400000) - (date.getTimezoneOffset() / 1440) + 2440588.5);
  }

  const handleHeDateChange = (start, end) => {
    console.log(start, end)
    setOptions(prev => ({
      ...prev,
      earned_start: JStoJulian(start.date),
      earned_end: JStoJulian(end.date)
    }));
  }

  const { school_id, class_id, rank, user_serial, current, hide_printed, permanent } = options;
  // generate dropdowns
  // const soldierOptions = this.getSoldierOptions();
  const rankOptions = getRankOptions();
  const isHQ = isAdmin(login.code);

  return (
    <div id='RankCardsPage'>
      <Callout title='Soldier rank cards' className='no-print'>
        <p>Please note that Headquarters will likely print and ship permenent ID cards to you.</p>
        <p>If you wish to print your own please make sure you can print <strong>3.37in x 2.12in</strong> ( 8.55cm x 5.38cm )</p>
        <p><strong>Please note that you can only print rank cards for registered soldiers.</strong></p>
        {is.firefox() &&
          <p>To get a print preview in FireFox please press the <FontAwesome icon='bars' /> button in the upper right corner and press "Print"</p>
        }
      </Callout>
      <p className='title no-print'>Options</p>
      <Row className='no-print'>
        {isHQ && // HQ only gets to pick a base
          <Col xs='12'>
            <label>Base</label>
            <BaseSelect value={school_id} onChange={handleSelectChange('school_id')} showAllOption />
          </Col>
        }
        <Col xs='6' sm='4'>
          <label>Platoon</label>
          <PlatoonSelect schoolId={school_id} value={class_id}
            onChange={handleSelectChange('class_id')} showAllOption />
        </Col>
        <Col xs='6' sm='4'>
          <label>Rank</label>
          <Select options={rankOptions} value={findOption(rankOptions, rank)}
            onChange={handleSelectChange('rank')} openMenuOnFocus />
        </Col>
        <Col xs='12' sm='4'>
          <label>Serial number</label>
          <Input name='user_serial' value={user_serial} onChange={handleInputChange} />
        </Col>
        <Col xs='12' sm='6'>
          <label>Show ranks:</label><br />
          <Radio
            value={true}
            name='current'
            checked={current}
            onChange={handleRadioChange}>
            Current rank only
          </Radio>
          <Radio
            value={false}
            name='current'
            checked={!current}
            onChange={handleRadioChange}>
            All ranks earned
          </Radio>
        </Col>
        <Col xs='12' sm='6'>
          <label>Print type:</label><br />
          <Radio
            value={false}
            name='permanent'
            checked={!permanent}
            onChange={handleRadioChange}>
            Temporary
          </Radio>
          <Radio
            value={true}
            name='permanent'
            checked={permanent}
            onChange={handleRadioChange}>
            Permanent
          </Radio>
        </Col>
        <Col xs='12' sm='6'>
          <label>Ranks earned on or before:</label><br />
          {/*<Date */}
          {/*  maxDate={ moment() }*/}
          {/*  value={ moment( julian.toDate( earned_before ) ) }*/}
          {/*  onChange={ this.handleDateChange('earned_before') } />*/}
          <HeDate
            onChange={handleHeDateChange}
          />
        </Col>
        <Col xs='12' sm='6'>
          <label>Other Options:</label><br />
          <Checkbox name='hide_printed' checked={hide_printed} onChange={handleCheckboxChange}>
            Hide Already Printed
          </Checkbox>
        </Col>
        <Col xs='12'>
          <ButtonBar>
            <Button color='primary' onClick={handleGetRankCards}>
              <FontAwesome icon='sync-alt' spin={loading} /> Generate Rank Cards
            </Button>
            {isHQ && cards.length > 0 && // mark all as printed
              <React.Fragment>
                <Button color='primary' onClick={handleMarkAllPrinted(true)}>
                  Mark All As Printed
                </Button>
                <Button color='primary' onClick={handleMarkAllPrinted(false)}>
                  Mark All As Not Printed
                </Button>
              </React.Fragment>
            }
            {isHQ && updates.length > 0 && // sync the updates array with the server
              <Button color='primary' onClick={syncPrinted}>
                <FontAwesome icon='save' /> Sync Printed Updates
              </Button>
            }
            <Button color='primary' onClick={print}>
              <FontAwesome icon='print' /> Print
            </Button>
          </ButtonBar>
        </Col>
      </Row>
      {loading && <Spinner size='8' />}
      {cards.length > 0 && !loading &&
        <div id='rank-card-results'>
          <p className='title no-print'>Rank Cards</p>
          <div id='rank-cards'>
            {cards.map((card, index) =>
              <RankCard key={index} user={card} permanent={permanent}
                showPrinted={isHQ} onChange={handlePrintedChange} />
            )}
          </div>
        </div>
      }
      {cards.length === 0 && loaded &&
        <Alert color="danger">No Rank Cards Found. Please change your options above.</Alert>
      }
    </div>
  )
}

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

export default connect(mapStateToProps)(RankCardsPage);
