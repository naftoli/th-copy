import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import PropTypes from 'prop-types';
// components
import TaskModal from './TaskModal';
import { Button } from 'reactstrap';
import { Link } from 'react-router-dom';
import { ButtonBar, Table, InlineSync, FontAwesome, NumberDisplay } from 'components/ui';
import { Toggle } from 'components/inputs';
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

export const TasksPage = () => {
  const dispatch = useDispatch();
  const { tasks, subjects, loading, login } = useSelector(({ rewards, login }) => ({
    ...rewards.achievement_tasks,
    subjects: rewards.subjects.subjects,
    login: login.current_login
  }));

  const [show, setShow] = useState(false);
  const [task, setTask] = useState({});

  const loadTasks = () => {
    showError(dispatch(getTasks()));
  }

  useEffect(() => {
    setTitle('Achievement Tasks');
    loadTasks();
  }, [dispatch]);

  const saveTask = taskData => {
    let action;
    // set the action
    if (taskData.achievement_task_id)
      action = dispatch(updateTask(taskData.achievement_task_id, taskData));
    else
      action = dispatch(createTask(taskData));
    // and handle it's results
    return action
      .catch(e => {
        toast.error(e.message, { autoClose: false });
        // Return a rejected promise to maintain original behavior if needed by caller,
        // although in functional component often we just handle side effects here.
        // But TaskModal might expect a promise.
        throw e;
      });
  }

  // toggle auction_only_points directly from table
  const toggleAuctionOnly = taskItem => (e) => {
    e.stopPropagation(); // prevent row selection if there is any
    const checked = e.target.checked;
    dispatch(updateTask(taskItem.achievement_task_id, {
      auction_only_points: checked ? 1 : 0
    }))
      .catch(err => toast.error(err.message, { autoClose: false }));
  }

  // toggle the master modal
  const toggle = (taskItem = {}) => {
    setShow(prev => !prev);
    setTask(taskItem);
  }

  // create and edit tasks
  const newTask = () => { toggle(); }
  // handle edit events
  const editTaskHandler = taskItem => () => { toggle(taskItem); }

  const toCSV = () => {
    const headers = [
      'Task', 'Subject', 'Miles', 'Base', 'Platoon',
    ];
    // get the data
    const rows = tasks.map(t => [
      t.task, t.subject && t.subject.subject_name,
      t.points, t.baseName, t.platoonName
    ]);
    return dataToCSV(headers, rows);
  }

  // Filter tasks
  const editableTasks = tasks.filter(t => t.editable);

  let columns = [
    {
      Header: 'Task', accessor: 'task',
      Cell: ({ value, original }) => {
        if (original.editable)
          return <a tabIndex={0} onClick={editTaskHandler(original)}>{value}</a>;
        return value;
      }
    },
    { Header: 'Miles', accessor: 'points', Cell: ({ value }) => <NumberDisplay value={value} /> },
    {
      Header: 'Auction Only Miles', id: 'auction_only_points', accessor: 'auction_only_points',
      filterable: true, sortable: true,
      width: 140,
      Cell: ({ original }) => (
        <div style={{ textAlign: 'center', display: 'flex', justifyContent: 'center', alignItems: 'center' }}>
          <Toggle
            id={`auction_only_points_${original.achievement_task_id}`}
            checked={!!original.auction_only_points}
            onChange={toggleAuctionOnly(original)}
            on='On'
            off='Off'
          />
        </div>
      )
    },
    { Header: 'Campaign', id: 'subject', accessor: ({ subject }) => subject && subject.subject_name },
  ];
  if (isAdmin(login.code)) columns.push(
    {
      Header: 'Base', accessor: 'baseName', Cell: ({ value, original }) => {
        if (original.base > 1)
          return <Link to={`/bm/base/${original.base}`}>{value}</Link>;
        return value;
      }
    }
  );

  // add the platoon column if a base is accessing the page
  if (isBC(login.code)) columns.push(
    {
      Header: 'Platoon', accessor: 'platoonName', Cell: ({ value, original }) => {
        if (original.platoon > 1)
          return <Link to={`/bm/platoons/${original.platoon}`}>{value}</Link>;
        return value;
      }
    }
  );

  return (
    <div id='TasksPage' className='full-height'>
      <ButtonBar>
        <Button onClick={newTask} className='btn btn-primary'>
          <FontAwesome icon='plus' /> Create Task
        </Button>
        <Button color='primary' onClick={loadTasks}>
          <InlineSync loading={loading} /> Refresh
        </Button>
        {canDownload(tasks) &&
          <CSVLink
            data={toCSV()}
            filename={"achievement_tasks.csv"}
            target="_blank"
          >
            <Button color='primary'>
              <FontAwesome icon='file-download' /> Download Tasks (CSV/Excel)
            </Button>
          </CSVLink>
        }
      </ButtonBar>

      <Table
        data={editableTasks} // logic: tasks = tasks.filter( task => task.editable ); in original render
        columns={columns}
        loading={loading && !tasks.length}
        pageId='TasksPage' />

      <TaskModal
        task={task}
        login={login}
        isOpen={show}
        toggle={toggle}
        subjects={subjects}
        onSubmit={saveTask} />

    </div>
  );
}

TasksPage.propTypes = {
  // tasks: PropTypes.array // proptypes are less critical in functional but can be kept if needed, though usually strict props are defined in TS or JSDoc.
  // omitting for brevity and standard hook pattern unless strict requirement.
};

export default TasksPage;
