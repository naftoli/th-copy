import React, { Component } from 'react';
import PropTypes from 'prop-types';
import { Collapse } from 'reactstrap';
import classnames from 'classnames';
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
    const items = this.props.items.map(
      (child, index) => <SidebarItem { ...child } key={index} />
    )
    const { icon, label } = this.props;
    const { collapse } = this.state;
    return (
      <li>
        <a onClick={ this.toggle } onKeyPress={ this.toggle } tabIndex={0}
          className={ classnames('dropdown', { open: collapse }) }>
          { icon }
          <span>{ label }</span>
        </a>
        <Collapse isOpen={ collapse }>
          <ul>{ items }</ul>
        </Collapse>
      </li>
    )
  }
}

SidebarDropdown.propTypes = {
  items: PropTypes.array.isRequired,
  icon: PropTypes.oneOfType([
    PropTypes.bool,
    PropTypes.element,
  ]),
  label: PropTypes.string.isRequired
}

SidebarDropdown.defaultProps = {
  items: [],
  icon: false,
  label: ""
}

export default SidebarDropdown;