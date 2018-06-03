import React, { Component } from 'react';
import SidebarItem from './SidebarItem.jsx';
import PropTypes from 'prop-types';
import './Sidebar.scss';

class Sidebar extends Component {
  render() {
    // create the menu of items
    const menu = this.props.menu.map(
      ( item, index ) => <SidebarItem item={item} key={index} level={1} />
    )

    const active = this.props.active ? "active" : "";

    return (
      <div id="sidebar" className={`${active}`} >
        <ul className="list-unstyled components">
          { menu }
        </ul>
      </div>
    )
  }
}

Sidebar.propTypes = {
  menu: PropTypes.array.isRequired,
  active: PropTypes.bool
}

Sidebar.defaultProps = {
  menu: [],
  active: false
}

export default Sidebar;