import React from 'react';
import { NavItem, NavLink } from 'reactstrap';
import classnames from 'classnames';

// navigation tab details
const NavigationTab = ( props ) => {
  const className = classnames({
    'active': props.active,
    [props.className]: !!props.className
  });
  return (
    <NavItem>
      <NavLink { ...props } className={className} >
        { props.children }
      </NavLink>
    </NavItem>
  );
}

export { NavigationTab }

export { default as Dashboard } from './dashboard/Dashboard';
export { default as Navbar } from './Navbar/Navbar';
export { default as Sidebar } from './Sidebar/Sidebar';
export { default as getMenu } from './Sidebar/menu';
