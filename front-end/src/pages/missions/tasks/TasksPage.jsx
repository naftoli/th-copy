import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
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
import { setTitle, canDownload } from 'functions/utils';
import { getTasks, createTask, updateTask } from 'store/missions/tasks/operations';
// styles
import './includes/tasks.scss';
// csv react component
import { CSVLink } from "react-csv";
import { dataToCSV } from 'functions/utils/csv';

export const TasksPage = () => {
  const dispatch = useDispatch();
  const { loading, tasks, login } = useSelector(({ login, missions }) => ({
    login: login.current_login,
    tasks: missions.tasks.tasks,
    loading: missions.tasks.loading
  }));

  const [editTask, setEditTask] = useState({});
  const [editModal, setEditModal] = useState(false);
  const [createModal, setCreateModal] = useState(false);

  // when the component mounts do some things
  useEffect(() => {
    setTitle('Mission Tasks');
    showError(dispatch(getTasks()));
  }, [dispatch]);

  const handleGetTasks = () => showError(dispatch(getTasks()));

  const handleCreateTask = task => showError(
    dispatch(createTask(task))
      .then(() => toast.info('Task created'))
      .then(() => setCreateModal(false))
  );

  const handleUpdateTask = task => showError(
    dispatch(updateTask(task))
      .then(() => setEditModal(false))
  );

  const edit = task => () => {
    setEditTask(task);
    setEditModal(true);
  };

  const toggleCreateModal = () => setCreateModal(prev => !prev);
  const toggleEditModal = () => setEditModal(prev => !prev);


  const toCSV = () => {
    const headers = [
      'Grid Id', 'Language Id', 'Campaign', 'Title', 'Details', 'Miles',
      'Category', 'On Mission Sheets', 'On Marking Grid', 'Mission Sheet Label', 'Base'
    ];
    // get the data
    const rows = tasks.map(task => [
      task.grid_id, task.lang_id, task.subject_id,
      task.short_name, task.name, task.points,
      task.cat, task.mission_marking ? 'Y' : 'N',
      task.grid_marking ? 'Y' : 'N', task.label_name,
      task.school_name
    ]);
    return dataToCSV(headers, rows);
  }

  const columns = getColumns(login, edit);

  return (
    <div id='TasksPage' className='full-height'>
      <ButtonBar>
        <Button
          color='primary'
          onClick={toggleCreateModal}>
          <FontAwesome icon='plus' /> Add Task
        </Button>

        <Button
          color='primary'
          onClick={handleGetTasks}>
          <InlineSync loading={loading.tasks} /> Refresh
        </Button>

        {canDownload(tasks) &&
          <CSVLink
            data={toCSV()}
            filename={"tasks.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Tasks (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={tasks}
        pageId='TasksPage'
        columns={columns}
        loading={loading.tasks && !tasks.length} />

      <AddTaskModal
        login={login}
        isOpen={createModal}
        saving={loading.saving}
        createTask={handleCreateTask}
        toggle={toggleCreateModal} />

      <EditTaskModal
        login={login}
        task={editTask}
        isOpen={editModal}
        saving={loading.updating}
        updateTask={handleUpdateTask}
        toggle={toggleEditModal} />

    </div>
  );
}

export default TasksPage;
