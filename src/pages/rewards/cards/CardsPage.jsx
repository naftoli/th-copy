import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import AchievementCard from './AchievementCard';
import { Row, Col, Input, Button, ButtonGroup } from 'reactstrap';
import { InlineSync, FontAwesome, Number, Callout } from 'components/ui';
import { SubjectSelect, AchievementTaskSelect, Date } from 'components/inputs';
// functions
import moment from 'moment';
import { toast } from 'react-toastify';
import { isTeacher, isBC, isAdmin } from 'functions/login';
import { setTitle } from 'functions/utils';
// state
import { getSubjects } from 'store/rewards/subjects/operations';
import { getTasks } from 'store/rewards/achievement_tasks/operations';
import { 
  getMiles, generateAchievementCards, deleteUnused
} from 'store/rewards/achievement_cards/operations';
// style
import './cards.scss';

class CardsPage extends Component {

  static defaultProps = {
    tasks: [],
    loading: false,
    subjects: []
  }

  state = { 
    subject_id: false,
    task_id: false,
    card_count: 10,
    delete_to: moment()
  };

  componentDidMount() {
    setTitle( 'Achievement Cards' );
    this.props.getMiles(); // load my miles limit
    this.loadSubjects(); // refresh the subjects
    this.loadTasks(); // refresh all tasks
  }

  // load all subjects
  loadSubjects = () => {
    this.props.getSubjects()
    .catch( e => toast.error( e.message ) );
  }
  // load all tasks
  loadTasks = () => {
    this.props.getTasks()
    .catch( e => toast.error( e.message ) );
  }
  // cards
  generateCards = e => {
    e.preventDefault();
    // do not keep generating tasks
    if ( this.props.loading ) return false;
    // validate input and generate cards
    const { subject_id, task_id, card_count } = this.state;
    const postData = { subject_id, task_id, card_count };
    // make sure a subject was selected
    if ( !postData.subject_id )
      return toast.error('Cannot create Achievement Cards without a Campaign.');
    // make sure a task was selected
    if ( !postData.task_id )
      return toast.error('Cannot create Achievement Cards without a Task.');
    if ( !postData.card_count )
      return toast.error('Cannot create unknown number of Achievement Cards.')
    
    return this.props.generateAchievementCards( postData )
    .catch( e => toast.error( e.message ) );
  }

  deleteUnused = () => {
    if ( window.confirm('Are you sure you want to delete these achievement cards? Once they are deleted the numbers will be recycled and they cannot be recovered.') ) {
      this.props.deleteUnused( this.state.delete_to.format() )
      .then( cards_deleted => toast.info(`${ cards_deleted } Achievement Cards Deleted.`) )
      .catch( e => toast.error( e.message ) );
    }
  }

  // event handlers
  onChange = ({ target }) => { this.setState({ [target.id]: target.value }) }
  onTaskChange = ({ value }) => { this.setState({ task_id: value }) }
  onSubjectChange = ({ value }) => { this.setState({ subject_id: value, task_id: false }) }
  onDateChange = date => this.setState({ delete_to: date });
  print = () => { window.print(); }

  render() {
    let { loading, miles, cards, login } = this.props;
    const { subject_id, task_id, card_count, delete_to } = this.state;

    const subjectFilter = subject => subject.tasks > 0;
    const taskFilter = task => {
      // limits based on rank
      if ( isBC( login.code ) && task.platoon > 1 )
        return false;
      
      if ( isAdmin( login.code ) && task.base > 1 )
        return false;
      
      return task.subject_id === subject_id;
    }

    let max = 1500;

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
              <Number value={ miles || 0 } /> Available Miles
            </h2>
          }

          <form onSubmit={ this.generateCards }>
            <Row id='options'>

              <Col sm={ 6 } xl={3}>
                <label>Campaign</label>
                <SubjectSelect 
                  showTasks
                  filter={ subjectFilter }
                  value={ subject_id }
                  onChange={ this.onSubjectChange } />
              </Col>

              <Col sm={ 6 } xl={3}>
                <label>Task</label>
                <AchievementTaskSelect 
                  showMiles
                  value={ task_id }
                  filter={ taskFilter }
                  onChange={ this.onTaskChange } />
              </Col>

              <Col sm={ 6 } xl={3}>
                <label htmlFor='card_count'># Of Cards</label>
                <Input
                  type='number' id='card_count' 
                  max={ max } min={ 1 } value={ card_count }
                  onChange={ this.onChange } required />
                <div className='invalid-message'>You can only create up to { max } cards at once.</div>
              </Col>

              <Col sm={ 6 } xl={3}>
                <ButtonGroup>
                  <Button color='primary'>
                    <InlineSync loading={ loading } /> Create
                  </Button>
                  <Button onClick={ this.print } className='btn btn-primary'>
                    <FontAwesome icon='print' /> Print
                  </Button>
                </ButtonGroup>
              </Col>

            </Row>
          </form>
        </div>

        <div id='cards'>
          { cards.map(
            ( card, index ) => <AchievementCard { ...card } logo={ login.img } key={ index } />
          ) }
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
              <ButtonGroup>
                <Button color='danger' onClick={ this.deleteUnused }>
                  <FontAwesome icon='trash' /> Delete Unused Cards
                </Button>
              </ButtonGroup>
            </Col>
          </Row>
        </div>
        
      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { achievement_cards } = rewards;
  return {
    ...achievement_cards,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  getSubjects, getTasks, deleteUnused,
  getMiles, generateAchievementCards
};

export default connect( mapStateToProps, mapDispatchToProps )( CardsPage );
