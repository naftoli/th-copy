import React, { useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
// componets
import { Callout } from 'components/ui';
import { TabContent, Nav } from 'reactstrap';
import { NavigationTab } from 'components/navigation';
// tabs
import DailyTab from './tabs/DailyTab';
import WeeklyTab from './tabs/WeeklyTab';
import TehillimTab from './tabs/TehillimTab';
import CustomizeModal from './includes/CustomizeModal';
// functions
import { toast } from 'react-toastify';
import { markTask, getGrid, customizeGrid } from 'store/missions/grid/operations';

export const TeacherMarkPage = () => {
  const dispatch = useDispatch();
  const { grid, login } = useSelector(({ missions, login }) => ({
    grid: missions.grid,
    login: login.current_login
  }));

  const { daily, weekly, tehillim } = grid;

  const [activeTab, setActiveTab] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [modalType, setModalType] = useState('daily');

  const toggleTab = tab => setActiveTab(tab);

  const handleGetGrid = (type, date) => {
    return dispatch(getGrid(type, date))
      .catch(e => toast.error(e.message));
  }

  const handleCustomizeGrid = (missions) => {
    return dispatch(customizeGrid(modalType, missions))
      .then(closeModal);
  }

  const handleMarkTask = (type, user_ids, grid_id, date, mark) => {
    return dispatch(markTask(type, user_ids, grid_id, date, mark))
      .catch(e => toast.error(e.message));
  }

  const openModal = type => () => {
    setModalOpen(true);
    setModalType(type);
  }

  const closeModal = () => {
    setModalOpen(false);
  }

  const navProps = { onClick: toggleTab, activeTab };
  const tabProps = {
    markTask: handleMarkTask,
    getGrid: handleGetGrid,
    openModal: openModal
  };

  return (
    <div id='TeacherMarkPage'>
      <Callout title='Mark Missions'>
        <p>The missions below are organized by how often they happen and displayed in an easy to mark grid for your convenience.</p>
        <p><strong>Please Note:</strong> Soldier's missions may be marked by their parents and base commanders as well.</p>
        <p><strong>
          Please Note: Not all soldiers have all the tasks below available to them.
          If a soldier does not have the task available to them the mark <em>will not save.</em>
        </strong></p>
      </Callout>

      <Nav tabs>

        <NavigationTab tab={1} icon='cloud-sun' {...navProps}>
          Daily
        </NavigationTab>

        <NavigationTab tab={2} icon='calendar' {...navProps}>
          Weekly
        </NavigationTab>

        {login.modules.tehillim &&
          <NavigationTab tab={3} icon='book' {...navProps}>
            Tehillim
          </NavigationTab>
        }

      </Nav>

      <TabContent activeTab={activeTab}>

        <DailyTab tabId={1} {...daily} {...tabProps} />

        <WeeklyTab tabId={2} {...weekly} {...tabProps} />

        {login.modules.tehillim &&
          <TehillimTab tabId={3} {...tehillim} {...tabProps} />
        }

      </TabContent>

      <CustomizeModal
        type={modalType}
        isOpen={modalOpen}
        toggle={closeModal}
        missions={grid[modalType]?.missions}
        customizeGrid={handleCustomizeGrid} />
    </div>
  );
}

export default TeacherMarkPage;
