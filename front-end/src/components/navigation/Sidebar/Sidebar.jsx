import React from 'react';
import SidebarItem from './SidebarItem.jsx';
import PropTypes from 'prop-types';
import './Sidebar.scss';

const Sidebar = ({ menu = [], active = false, minimized = false }) => {
  // create the menu of items
  const renderedMenu = menu.map(
    (item, index) => <SidebarItem {...item} key={index} />
  )

  const activeClass = active ? "active" : "";
  const minimizedClass = minimized ? "minimized" : "";
  const sidebarStyle = { scrollbarWidth: 'none' }

  return (
    <div id="sidebar" className={`${activeClass} ${minimizedClass}`} style={sidebarStyle} >
      <ul className="list-unstyled components">
        {renderedMenu}
      </ul>
    </div>
  )
}

Sidebar.propTypes = {
  menu: PropTypes.array.isRequired,
  active: PropTypes.bool
}

export default Sidebar;