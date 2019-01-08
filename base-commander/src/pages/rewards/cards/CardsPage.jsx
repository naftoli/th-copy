import React, { Component } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL } from 'components/constants';
// components
import { Row, Col, Input, Button } from 'reactstrap';
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

class CardsPage extends Component {
  // default props
  static defaultProps = {
    login: {},
    miles: false,
  }
  // initial state
  state = { 
    subject_id: false,
    task_id: false,
    card_count: 10,
    delete_to: moment()
  };

  // when the page loads, get some data
  componentDidMount() {
    setTitle( 'Achievement Cards' );
    this.props.getMiles(); // load my miles limit
    showError( this.props.getTasks() );
  }

  // delete the unused cards
  deleteUnusedCards = () => {
    if ( window.confirm('Are you sure you want to delete these achievement cards? Once they are deleted the numbers will be recycled and they cannot be recovered.') ) {
      this.props.deleteUnusedCards( this.state.delete_to.format() )
      .then( cards_deleted => toast.info(`${ cards_deleted } Achievement Cards Deleted.`) )
      .catch( e => toast.error( e.message ) );
    }
  }

  onSubmit = () => {
    setTimeout( this.props.getMiles, 1000 );
  }

  // event handlers
  onChange = ({ target }) => { this.setState({ [target.id]: target.value }) }
  onTaskChange = ({ value }) => { this.setState({ task_id: value }) }
  onSubjectChange = ({ value }) => { this.setState({ subject_id: value, task_id: false }) }
  onDateChange = date => this.setState({ delete_to: date });

  // filters
  subjectFilter = subject => subject.achievement_tasks.length > 0;

  render() {
    let { miles, login, tasks } = this.props;
    const { subject_id, task_id, card_count, delete_to } = this.state;

    let max = 100000; // 100,000 ( insanely high limit )
    let disabled = !subject_id || !task_id || !card_count;
    const task = tasks.find( task => task.achievement_task_id === task_id );

    // if we have a task and miles, update the max and disabled
    if ( task && miles ) {
      max = Math.floor( miles / task.points );
      disabled = disabled || miles < task.points * card_count;
    }
    // disable the button if miles is less then 0
    if ( typeof miles === 'number' )
      disabled = disabled || miles <= 0;
    // print the page
    return (
      <div id='AchievementsCardsPage'>
        <div className='no-print'>
          <Callout title='Achievement Cards'>
            <p>Print cards for your tasks to give out to your soldiers to spend on rewards in your store.</p>
            { isTeacher( login.code ) && 
              <p>Please note that once you generate the cards the miles will be subtracted from your available miles.</p>
            }
          </Callout>

          <p className='title'>Generate cards</p>

          { isTeacher( login.code ) && 
            <h2 id='available-miles'>
              <NumberDisplay value={ miles || 0 } /> Available Miles
            </h2>
          }

          <form target='_blank' method='post' onSubmit={ this.onSubmit }
              action={`${LEGACY_URL}/api/print/achievement_cards`}>
            <Row id='options'>
              <Col sm={ 6 } xl={3}>
                <label>Campaign</label>
                <RewardSubjectSelect
                  required
                  showTasks
                  name='subject_id'
                  value={ subject_id }
                  filter={ this.subjectFilter }
                  onChange={ this.onSubjectChange } />
              </Col>

              <Col sm={ 6 } xl={3}>
                <label>Task</label>
                <AchievementTaskSelect 
                  required
                  showMiles
                  name='task_id'
                  value={ task_id }
                  subjectId={ subject_id }
                  onChange={ this.onTaskChange } />
              </Col>

              <Col sm={ 6 } xl={3}>
                <label htmlFor='card_count'># Of Cards</label>
                <Input
                  required        type='number'   id='card_count'   
                  max={ max }     min={ 1 }       name='card_count' 
                  onChange={ this.onChange }      value={ card_count } />
                <div className='invalid-message'>
                  You can only create up to { max } cards.
                </div>
              </Col>

              <Col sm={ 6 } xl={3}>
                <Button color='primary' disabled={ disabled }>
                  <FontAwesome icon='print' /> Print
                </Button>
              </Col>
            </Row>
          </form>
        </div>

        <div className='no-print'>
          <p className='title'>Delete Unused Cards</p>
          <Row id='delete'>
            <Col sm={ 6 }>
              <label>Printed On Or Before</label>
              <Date 
                maxDate={ moment() }
                value={ delete_to } 
                onChange={ this.onDateChange } />
            </Col>
            <Col sm={ 6 }>
              <Button color='danger' onClick={ this.deleteUnusedCards }>
                <FontAwesome icon='trash' /> Delete Unused Cards
              </Button>
            </Col>
          </Row>
        </div>
      </div>
    );
  }
}

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

export default connect( mapStateToProps, mapDispatchToProps )( CardsPage );
