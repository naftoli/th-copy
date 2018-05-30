import React, { Component } from 'react';
import { LEGACY_URL } from './menu';
import { Collapse } from 'reactstrap';

class SidebarItem extends Component {
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
    let children = false;
    // this is a menu
    if ( this.props.item.children ) {
      children = this.props.item.children.map( child => <SidebarItem item={ child } />)
      return (
        <li>
          <a onClick={ this.toggle } className={ this.state.collapse ? "open" : "" }>
            { this.props.item.icon }
            <span>{ this.props.item.label }</span>
          </a>
          <Collapse isOpen={ this.state.collapse }>
            { children }
          </Collapse>
        </li>
      )
    // this is an endpoint
    } else {
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
}

export default SidebarItem;