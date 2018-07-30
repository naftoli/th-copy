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
