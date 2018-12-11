import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Button } from 'reactstrap';
import { FontAwesome, Table, ButtonBar, InlineSync } from 'components/ui';
// modals
import AddTaskModal from './AddTaskModal';
import EditTaskModal from './EditTaskModal';
// functions
import { toast } from 'react-toastify';
import getColumns from './includes/columns';
import { showError } from 'functions/notifications';
import { arrayToCSV, setTitle, canDownload } from 'functions/utils';
import { getTasks, createTask, updateTask } from 'store/missions/tasks/operations';
// styles
import './includes/tasks.scss';

class TasksPage extends Component {

  state = {
    editTask: {},
    editModal: false,
    createModal: false,
  }
  // when the component mounts do some things
  componentDidMount(){
    setTitle( 'Mission Tasks' );
    this.getTasks();
  }
  // get the tasks
  getTasks = () =>
    showError( this.props.getTasks() );

  // get the tasks
  createTask = task => showError(
    this.props.createTask( task )
    .then( () => toast.info('Task created') )
    .then( () => this.setState({ createModal: false }) )
  );

  // update a single task
  updateTask = task => showError(
    this.props.updateTask( task )
    .then( () => this.setState({ editModal: false }) )
  );

  // edit a single task
  edit = editTask => () =>
    this.setState({ editTask, editModal: true });

  // toggle the modal
  toggleModal = modal => () =>
    this.setState({ [ modal ]: !this.state[ modal ] });
  
  // CSV export
  toCSV = () => {
    const headers = [
      'Grid Id', 'Language Id', 'Campaign', 'Title', 'Details', 'Miles', 
      'Category', 'On Mission Sheets', 'On Marking Grid', 'Mission Sheet Label', 'Base'
    ];
    // get the data
    const rows = this.props.tasks.map( task => [
      task.grid_id,     task.lang_id,   task.subject_id,
      task.short_name,  task.name,      task.points,
      task.cat,         task.mission_marking ? 'Y' : 'N',
      task.grid_marking ? 'Y' : 'N',    task.label_name,
      task.school_name
    ]);
    arrayToCSV( headers, rows, 'mission_tasks' );
  }

  render() {
    const { loading, tasks, login } = this.props;
    const { createModal, editModal, editTask } = this.state;

    const columns = getColumns( login, this.edit );

    return (
      <div id='TasksPage' className='full-height'>
        <ButtonBar>
          <Button
              color='primary'
              onClick={ this.toggleModal('createModal') }>
           <FontAwesome icon='plus' /> Add Task
          </Button>

          <Button
              color='primary'
              onClick={ this.getTasks }>
            <InlineSync loading={ loading.tasks }/> Refresh
          </Button>

          { canDownload( tasks ) &&
            <Button color='primary' onClick={ this.toCSV }>
              <FontAwesome icon='file-download' /> Download Tasks (CSV/Excel)
            </Button>
          }
        </ButtonBar>

        <Table 
          data={ tasks }
          pageId='TasksPage'
          columns={ columns }
          loading={ loading.tasks && !tasks.length } />

        <AddTaskModal
          login={ login }
          isOpen={ createModal }
          saving={ loading.saving }
          createTask={ this.createTask }
          toggle={ this.toggleModal('createModal') } />

        <EditTaskModal
          login={ login }
          task={ editTask }
          isOpen={ editModal }
          saving={ loading.updating }
          updateTask={ this.updateTask }
          toggle={ this.toggleModal('editModal') } />

      </div>
    );
  }
}

const mapStateToProps = ({ login, missions }) => {
  return {
    login: login.current_login,
    ...missions.tasks
  }
}

const mapDispatchToProps = {
  createTask,   getTasks,
  updateTask
}

export default connect( mapStateToProps, mapDispatchToProps )( TasksPage );
