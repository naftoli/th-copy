import React, { Component } from 'react';
import { LEGACY_URL } from './menu';
import SidebarDropdown from './SidebarDropdown';

class SidebarItem extends Component {

  render(){
    // This is a dropdown
    if ( this.props.item.children ) {
      return <SidebarDropdown { ...this.props.item } />;
    }

    // Generate the correct type of link
    let path = "#";

    // generate the path to the correct location
    if ( this.props.item.legacy ){
      path = LEGACY_URL + this.props.item.path;
    } else {
      // return react router link
    }

    // return a simple item
    return (
      <li>
        <a href={ path }>
          { this.props.item.icon }
          { this.props.item.label }
        </a>
      </li>
    )
  }
}

export default SidebarItem;