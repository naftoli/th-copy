import React, { Component } from 'react';
import PropTypes from 'prop-types';
import SidebarItem from './SidebarItem';
import menu from './menu';
import 'styles/navigation/Sidebar.css';

class Sidebar extends Component {
  constructor( props ){
    super( props );
    this.state = { menu }
  }

  render() {
    const menu = this.state.menu.map( ( item, index ) => <SidebarItem item={item} key={index} /> )
    return (
      <div id="sidebar" className="active">
        <ul className="list-unstyled components">
          { menu }
        </ul>
      </div>
    )
  }
}

export default Sidebar;
