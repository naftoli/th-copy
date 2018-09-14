import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import TaskModal from './TaskModal';
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import { ButtonBar, Table, InlineSync, FontAwesome, Number } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isBC, isAdmin } from 'functions/login';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
// state
import { getSubjects } from 'store/rewards/subjects/operations';
import { 
  getTasks, updateTask, createTask 
} from 'store/rewards/achievement_tasks/operations';
// style
import './tasks.scss';
import { Promise } from 'core-js';

class TasksPage extends Component {

  static propTypes = {
    tasks: PropTypes.array
  };

  static defaultProps = {
    tasks: [],
    loading: false,
  }

  state = { 
    show: false,
    task: {}
  };

  componentDidMount() {
    setTitle( 'Achievement Tasks' );
    this.loadSubjects();
    this.loadTasks(); 
  }

  // network
  loadSubjects = () => {
    this.props.getSubjects()
    .catch( e => toast.error( e.message ) );
  }

  loadTasks = () => {
    this.props.getTasks()
    .catch( e => toast.error( e.message ) );
  }

  saveTask = task => {
    let action;
    // set the action
    if ( task.achievement_task_id )
      action = this.props.updateTask( task.achievement_task_id, task );
    else
      action = this.props.createTask( task );
    // and handle it's results
    return action
    .catch( e => {
      toast.error( e.message, { autoClose: false } );
      return Promise.reject( e );
    });
  }

  // toggle the master modal
  toggle = ( task = {} ) => {
    this.setState({
      show: !this.state.show, 
      task: task
    });
  }
  // create and edit tasks
  newTask = () => { this.toggle(); }
  // handle edit events
  editTaskHandler = task => () => { this.toggle( task ); }

  toCSV = () => {
    const headers = [
      'Task', 'Subject', 'Miles', 'Base', 'Platoon', 
    ];
    // get the data
    const rows = this.props.bases.map( task => [
      task.task, task.subject && task.subject.subject_name, 
      task.points, task.baseName, task.platoonName
    ]);
    arrayToCSV( headers, rows, 'achievement_tasks' );
  }

  render() {
    let { tasks, loading, subjects, login } = this.props;
    const { show, task } = this.state;

    tasks = tasks.filter( task => task.editable );

    let columns = [
      { Header: 'Task', accessor: 'task',
        Cell: ({ value, original }) => {
          if ( original.editable )
            return <a tabIndex={ 0 } onClick={ this.editTaskHandler( original )}>{value}</a>;
          return value;
        }
      },
      { Header: 'Miles', accessor: 'points', Cell: ({ value }) => <Number value={ value }/> },
      { Header: 'Campaign', id: 'subject', accessor: ({ subject }) => subject && subject.subject_name },
    ];
    // add the platoon column if a base is accessing the page
    if ( isBC( login.code ) ) columns.push(
      { 
        Header: 'Platoon', accessor: 'platoonName', Cell: ({ value, original }) => {
          if ( original.platoon > 1 )
            return <Link to={`/bm/platoons/${original.platoon}`}>{ value }</Link>;
          return value;
        }
      }
    );

    return (
      <div id='TasksPage'>
      
        <ButtonBar>
          <Button onClick={this.newTask} className='btn btn-primary'>
            <FontAwesome icon='plus' /> Create Task
          </Button>
          <Button color='primary' onClick={ this.loadTasks }>
            <InlineSync loading={ loading } /> Refresh
          </Button>
          { canDownload( tasks ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Tasks (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          data={ tasks } 
          columns={ columns } 
          loading={ loading && !tasks.length } 
          pageId='TasksPage' />

        <TaskModal 
          task={ task }
          login={ login }
          isOpen={ show }
          toggle={ this.toggle }
          subjects={ subjects }
          onSubmit={ this.saveTask } />

      </div>
    );
  }
}

const mapStateToProps = ({ rewards, login }) => {
  const { achievement_tasks, subjects } = rewards;
  return {
    ...achievement_tasks,
    subjects: subjects.subjects,
    login: login.current_login
  }
};

const mapDispatchToProps = {
  getSubjects, getTasks, updateTask, createTask
};

export default connect(
  mapStateToProps, mapDispatchToProps
)( TasksPage );
