import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { InlineSync, FontAwesome, Number } from 'components/ui';
import { Row, Col, Input, Button, ButtonGroup } from 'reactstrap';
import { SubjectSelect, AchievementTaskSelect } from 'components/inputs';
// functions
import { toast } from 'react-toastify';
import { isTeacher } from 'functions/login';
import { setTitle } from 'functions/utils';
// state
import { getSubjects } from 'store/rewards/subjects/operations';
import { getTasks } from 'store/rewards/achievement_tasks/operations';
// style
import './cards.scss';
import Callout from 'components/ui/Callout';

class CardsPage extends Component {

  // static propTypes = {
  //   tasks: PropTypes.array,
  //   subjects: PropTypes.array
  // };

  static defaultProps = {
    tasks: [],
    loading: false,
    subjects: []
  }

  state = { 
    subject_id: false,
    task_id: false,
    cards: 10
  };

  componentDidMount() {
    setTitle( 'Achievement Cards' );
    this.loadSubjects();
    this.loadTasks(); 
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
    debugger;
  }

  // event handlers
  onChange = ({ target }) => { this.setState({ [target.id]: target.value }) }
  onTaskChange = ({ value }) => { this.setState({ task_id: value }) }
  onSubjectChange = ({ value }) => { this.setState({ subject_id: value }) }

  render() {
    let { tasks, subjects, login } = this.props;
    const { subject_id, task_id, cards } = this.state;

    const subjectFilter = subject => subject.tasks > 0;
    const taskFilter = task => task.subject_id === subject_id;

    let max = 200;

    return (
      <div id='AchievementsCardsPage'>

        <Callout title='Achievement Cards'>
          <p>Print cards for your tasks to give out to your soldiers to spend on rewards in your store.</p>
          { isTeacher( login.code ) && 
            <p>Please note that once you generate the cards the miles will be subtracted from your available miles.</p>
          }
        </Callout>

        { isTeacher( login.code ) && 
          <h2 id='available-miles'>
            <Number value={ 5000 } /> Available Miles
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
              <label htmlFor='cards'># Of Cards</label>
              <Input
                type='number' id='cards' 
                max={ max } min={ 1 } value={ cards }
                onChange={ this.onChange } required />
              <div className='invalid-message'>You can only generate 1 to { max } cards at once.</div>
            </Col>

            <Col sm={ 6 } xl={3}>
              <ButtonGroup>
                <Button color='primary'>
                  <InlineSync /> Generate
                </Button>
                <Button onClick={ this.print } className='btn btn-primary'>
                  <FontAwesome icon='print' /> Print
                </Button>
              </ButtonGroup>
            </Col>

          </Row>
        </form>
      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { achievement_cards } = rewards;
  return {
    cards: achievement_cards,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  getSubjects, getTasks
};

export default connect( mapStateToProps, mapDispatchToProps )( CardsPage );
