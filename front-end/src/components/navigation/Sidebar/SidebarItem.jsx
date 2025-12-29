import React from 'react';
import { LEGACY_URL } from 'components/constants';

import { NavLink } from 'react-router-dom'
import SidebarDropdown from './SidebarDropdown';
import { FontAwesome } from 'components/ui/Icons';

const SidebarItem = (props) => {
  let { items, icon, legacy, label, path } = props;

  // This is a dropdown
  if (items) {
    return <SidebarDropdown {...props} />;
  }

  if (icon && !legacy) {
    icon = <FontAwesome icon={icon} />
  } else if (icon) {
    icon = <img src={LEGACY_URL + icon} alt={label} />
  }

  // Generate link to React-Router page
  let link = (
    <NavLink end to={path}>
      {icon}<span>{label}</span>
    </NavLink>
  );

  // use a standard A tag if page is outside of this system
  if (legacy) {
    link = (
      <a href={LEGACY_URL + path}>
        {icon}<span>{label}</span>
      </a>
    );
  }

  // return the link wrapped in an LI
  return <li> {link} </li>;
}

SidebarItem.defaultProps = {
  label: '',
  icon: false,
  items: false,
  legacy: false,
  path: '#'
}

export default SidebarItem;