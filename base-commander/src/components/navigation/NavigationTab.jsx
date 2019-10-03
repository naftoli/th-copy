import React, { Component } from 'react';
import { FontAwesome } from 'components/ui';
import { NavItem, NavLink } from 'reactstrap';

export class NavigationTab extends Component {

  static defaultProps = {
    valid: true
  }

  click = e => {
    const { activeTab, tab, disabled, onClick } = this.props;
    // if we have the tab props set, attempt to just jump to that tab
    if ( activeTab && tab ) {
      if ( !disabled )
        return onClick( tab );
    }
    // return the standard onclick event
    return onClick( e );
  }

  onKeyPress = e => {
    if ( e.key === 'Enter' ) {
      this.click( e );
    }
  }

  render() {
    let {
      tab,  active, disabled, activeTab,  title,
      icon, valid,  children, ...props
    } = this.props;
    // if we have these props, update the active prop
    if ( activeTab && tab ) {
      active = activeTab === tab;
    }

    const tabIndex = disabled ? -1 : 0;

    return (
      <NavItem>
        <NavLink     { ...props }   active={ active }
            disabled={ disabled }   onClick={ this.click }
            tabIndex={ tabIndex }   onKeyPress={ this.onKeyPress }>

          { !valid && <FontAwesome icon='exclamation' /> }

          {' '}{ title || children }{' '}

          { icon && <FontAwesome icon={ icon } /> }
          
        </NavLink>
      </NavItem>
    );
  }
}

export const NavigationTabs = ({ tabs }) => {
  return tabs.map( ( tab, index ) =>
    <NavigationTab { ...tab } key={ index } />
  );
}
