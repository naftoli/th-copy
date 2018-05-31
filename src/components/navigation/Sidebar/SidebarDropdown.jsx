import React, { Component } from 'react';
import { Collapse } from 'reactstrap';
import SidebarItem from './SidebarItem';

class SidebarDropdown extends Component {
  constructor( props ){
    super( props );
    this.state = { collapse: false }
  }
  
  toggle = () => {
    this.setState({
      collapse: !this.state.collapse
    });
  }

  render(){
    const children = this.props.children.map(
      (child, index) => <SidebarItem item={ child } key={index} />
    )

    return (
      <li>
        <a onClick={ this.toggle } className={`dropdown ${ this.state.collapse ? "open" : "" }` }>
          { this.props.icon }
          <span>{ this.props.label }</span>
        </a>
        <Collapse isOpen={ this.state.collapse }>
          <ul>
            { children }
          </ul>
        </Collapse>
      </li>
    )
  }
}

export default SidebarDropdown;