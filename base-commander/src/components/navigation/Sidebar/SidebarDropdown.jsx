import React, { Component } from 'react';
import PropTypes from 'prop-types';

import classnames from 'classnames';
import { LEGACY_URL } from 'components/constants';

import { Collapse } from 'reactstrap';
import SidebarItem from './SidebarItem';
import { FontAwesome } from 'components/ui/Icons';

class SidebarDropdown extends Component {

  static propTypes = {
    items: PropTypes.array.isRequired,
    icon: PropTypes.oneOfType([
      PropTypes.bool,
      PropTypes.string,
    ]),
    label: PropTypes.string.isRequired
  }

  static defaultProps = {
    items: [],
    icon: false,
    label: ""
  }

  state = {
    collapse: false
  }
  
  toggle = () => {
    this.setState({
      collapse: !this.state.collapse
    });
  }

  render(){
    const { collapse } = this.state;
    let { icon, label, legacy, items } = this.props;
    
    items = items.map( (child, index) =>
      <SidebarItem { ...child } key={index} />
    );

    if ( icon && !legacy ) {
      icon = <FontAwesome icon={ icon } />
    } else if ( icon ) {
      icon = <img src={ LEGACY_URL + icon } alt={ label } />
    }

    return (
      <li>
        <a onClick={ this.toggle } onKeyPress={ this.toggle } tabIndex={ 0 }
            className={ classnames( 'dropdown', { open: collapse } ) }>
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

export default SidebarDropdown;
