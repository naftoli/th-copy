import React from 'react';
import classnames from 'classnames';
import { FontAwesome } from 'components/ui';
import { NavItem, NavLink } from 'reactstrap';

const NavigationTab = ( initialProps ) => {
  let { 
    onClick,  active, className,  activeTab,
    children, tab,    valid,      icon,     ...props
  } = initialProps;

  className = classnames({
    'active': !!active,
    [className]: !!className
  });

  const toggle = () => onClick( tab );

  if ( activeTab && tab )
    active = activeTab === tab;

  const click = activeTab && tab ? toggle : onClick;

  const onKeyPress = ( event ) => {
    if ( event.key === 'Enter' )
      click();
  }

  return (
    <NavItem>
      <NavLink { ...props } 
          tabIndex = { 0 }
          active = { active }
          onClick = { click } 
          className = { className } 
          onKeyPress = { onKeyPress }>
        { children }{' '}
        { icon && <FontAwesome icon={ icon } /> }
      </NavLink>
    </NavItem>
  );
}

export { NavigationTab }