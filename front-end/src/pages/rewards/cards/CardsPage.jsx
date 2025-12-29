import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input, Button, FormFeedback } from 'reactstrap';
import { FontAwesome, NumberDisplay, Callout } from 'components/ui';
import { RewardSubjectSelect, AchievementTaskSelect, Date } from 'components/inputs';
// functions
import moment from 'moment';
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { showError } from 'functions/notifications';
import { isTeacher } from 'functions/login';
// state
import { getTasks } from 'store/rewards/achievement_tasks/operations';
import { getMiles, deleteUnusedCards } from 'store/rewards/miles/operations';
// style
import './cards.scss';

const CardsPage = (props) => {
  const { miles, login, tasks, getMiles, getTasks, deleteUnusedCards } = props;

  // state
  const [subject_id, setSubjectId] = useState(false);
  const [task_id, setTaskId] = useState(false);
  const [card_count, setCardCount] = useState(10);
  const [delete_to, setDeleteTo] = useState(moment());
  const [touched, setTouched] = useState(false);

  // Initial load
  useEffect(() => {
    setTitle('Achievement Cards');
    getMiles();
    showError(getTasks());
  }, []);

  // delete the unused cards
  const handleDeleteUnusedCards = () => {
    if (window.confirm('Are you sure you want to delete these achievement cards? Once they are deleted the numbers will be recycled and they cannot be recovered.')) {
      deleteUnusedCards(delete_to.format())
        .then(cards_deleted => toast.info(`${cards_deleted} Achievement Cards Deleted.`))
        .catch(e => toast.error(e.message));
    }
  };

  const onSubmit = () => {
    setTimeout(getMiles, 1000);
  };

  // event handlers
  const onChange = ({ target }) => {
    if (target.id === 'card_count') setCardCount(target.value);
  };
  const onTaskChange = ({ value }) => setTaskId(value);
  const onSubjectChange = ({ value }) => {
    setSubjectId(value);
    setTaskId(false);
  };
  const onDateChange = date => setDeleteTo(date);
  const onBlur = () => setTouched(true);

  // filters
  const subjectFilter = subject => subject.achievement_tasks.length > 0;

  let max = 100000; // 100,000 ( insanely high limit )
  let disabled = !subject_id || !task_id || !card_count;
  const task = tasks.find(task => task.achievement_task_id === task_id);

  // if we have a task and miles, update the max and disabled
  if (task && miles) {
    max = Math.floor(miles / task.points);
    if (miles < task.points * card_count) disabled = true;
  }
  // disable the button if miles is less then 0
  if (typeof miles === 'number' && miles <= 0)
    disabled = true;

  const isInvalidCount = touched && (card_count > max || card_count < 1);

  return (
    <div id='AchievementsCardsPage'>
      <div className='no-print'>
        <Callout title='Achievement Cards'>
          <p>Print cards for your tasks to give out to your soldiers to spend on rewards in your store.</p>
          {isTeacher(login.code) &&
            <p>Please note that once you generate the cards the miles will be subtracted from your available miles.</p>
          }
        </Callout>

        <p className='title'>Generate cards</p>

        {isTeacher(login.code) &&
          <h2 id='available-miles'>
            <NumberDisplay value={miles || 0} /> Available Miles
          </h2>
        }

        <form target='_blank' method='post' onSubmit={onSubmit}
          action={`${LEGACY_URL}/api/print/achievement_cards`}>
          <Row id='options'>
            <Col sm={6} xl={3}>
              <label>Campaign</label>
              <RewardSubjectSelect
                required
                showTasks
                name='subject_id'
                value={subject_id}
                filter={subjectFilter}
                onChange={onSubjectChange} />
            </Col>

            <Col sm={6} xl={3}>
              <label>Task</label>
              <AchievementTaskSelect
                required
                showMiles
                name='task_id'
                value={task_id}
                subjectId={subject_id}
                onChange={onTaskChange} />
            </Col>

            <Col sm={6} xl={3}>
              <label htmlFor='card_count'># Of Cards</label>
              <Input
                required type='number' id='card_count'
                max={max} min={1} name='card_count'
                onChange={onChange} value={card_count}
                onBlur={onBlur}
                invalid={isInvalidCount}
              />
              <FormFeedback>
                You can only create up to {max} cards.
              </FormFeedback>
            </Col>

            <Col sm={6} xl={3}>
              <Button color='primary' disabled={disabled}>
                <FontAwesome icon='print' /> Print
              </Button>
            </Col>
          </Row>
        </form>
      </div>

      <div className='no-print'>
        <p className='title'>Delete Unused Cards</p>
        <Row id='delete'>
          <Col sm={6}>
            <label>Printed On Or Before</label>
            <Date
              maxDate={moment()}
              value={delete_to}
              onChange={onDateChange} />
          </Col>
          <Col sm={6}>
            <Button color='danger' onClick={handleDeleteUnusedCards}>
              <FontAwesome icon='trash' /> Delete Unused Cards
            </Button>
          </Col>
        </Row>
      </div>
    </div>
  );
};

const mapStateToProps = ({ rewards, login }) => {
  return {
    ...rewards.miles,
    login: login.current_login,
    tasks: rewards.achievement_tasks.tasks
  }
};

const mapDispatchToProps = {
  getTasks, deleteUnusedCards, getMiles
};

export default connect(mapStateToProps, mapDispatchToProps)(CardsPage);
