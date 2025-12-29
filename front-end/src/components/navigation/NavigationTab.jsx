import React from 'react';
import { FontAwesome } from 'components/ui';
import { NavItem, NavLink } from 'reactstrap';

export const NavigationTab = ({
  valid = true,
  activeTab, tab, active: activeProp, disabled, onClick,
  title, icon, children, ...props
}) => {

  const click = e => {
    // if we have the tab props set, attempt to just jump to that tab
    if (activeTab && tab) {
      if (!disabled)
        return onClick(tab);
    }
    // return the standard onclick event
    return onClick(e);
  }

  const handleKeyPress = e => {
    if (e.key === 'Enter') {
      click(e);
    }
  }

  // if we have these props, update the active prop
  let active = activeProp;
  if (activeTab && tab) {
    active = activeTab === tab;
  }

  const tabIndex = disabled ? -1 : 0;

  return (
    <NavItem>
      <NavLink {...props} active={active}
        disabled={disabled} onClick={click}
        tabIndex={tabIndex} onKeyPress={handleKeyPress}>

        {!valid && <FontAwesome icon='exclamation' />}

        {' '}{title || children}{' '}

        {icon && <FontAwesome icon={icon} />}

      </NavLink>
    </NavItem>
  );
}

export const NavigationTabs = ({ tabs }) => {
  return tabs.map((tab, index) =>
    <NavigationTab {...tab} key={index} />
  );
}
