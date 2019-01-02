import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// components
import TaskModal from './TaskModal';
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import { ButtonBar, Table, InlineSync, FontAwesome, NumberDisplay } from 'components/ui';
// functions
import { toast } from 'react-toastify';
import { isBC, isAdmin } from 'functions/login';
import { setTitle, canDownload } from 'functions/utils';
// state
import { 
  getTasks, updateTask, createTask 
} from 'store/rewards/achievement_tasks/operations';
// style
import './tasks.scss';
import { Promise } from 'core-js';
import { showError } from 'functions/notifications';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

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
    this.loadTasks(); 
  }

  loadTasks = () => {
    showError( this.props.getTasks() );
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
    //arrayToCSV( headers, rows, 'achievement_tasks' );
    return dataToCSV( headers, rows );
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
      { Header: 'Miles', accessor: 'points', Cell: ({ value }) => <NumberDisplay value={ value }/> },
      { Header: 'Campaign', id: 'subject', accessor: ({ subject }) => subject && subject.subject_name },
    ];
    if ( isAdmin( login.code ) ) columns.push(
      {
        Header: 'Base', accessor: 'baseName', Cell: ({ value, original }) => {
          if ( original.base > 1 )
            return <Link to={`/bm/base/${original.base}`}>{ value }</Link>;
          return value;
        }
      }
    );
      
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
            <CSVLink
              data = { this.toCSV() }
              filename = { "achievement_tasks.csv" }
              target = "_blank"
            >
              <Button color='primary'>
                <FontAwesome icon='file-download' /> Download Tasks (CSV/Excel)              
              </Button>
            </CSVLink>
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
  getTasks, updateTask, createTask
};

export default connect(
  mapStateToProps, mapDispatchToProps
)( TasksPage );
