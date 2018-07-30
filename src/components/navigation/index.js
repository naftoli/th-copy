import React from 'react';
import { NavItem, NavLink } from 'reactstrap';

// navigation tab details
const NavigationTab = ( props ) => (
  <NavItem>
    <NavLink { ...props } >
      { props.children }
    </NavLink>
  </NavItem>
);

export { NavigationTab }
