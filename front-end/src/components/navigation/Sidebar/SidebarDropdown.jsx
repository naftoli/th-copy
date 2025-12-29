import React, { useState } from 'react';
import PropTypes from 'prop-types';

import classnames from 'classnames';
import { LEGACY_URL } from 'components/constants';

import { Collapse } from 'reactstrap';
import SidebarItem from './SidebarItem';
import { FontAwesome } from 'components/ui/Icons';

const SidebarDropdown = ({
  items = [], icon, label, legacy, ...props
}) => {

  const [collapse, setCollapse] = useState(false);

  const toggle = () => {
    setCollapse(!collapse);
  }

  const renderedItems = items.map((child, index) =>
    <SidebarItem {...child} key={index} />
  );

  let iconElement = icon;
  if (icon && !legacy) {
    iconElement = <FontAwesome icon={icon} />
  } else if (icon) {
    iconElement = <img src={LEGACY_URL + icon} alt={label} />
  }

  return (
    <li>
      <a onClick={toggle} onKeyPress={toggle} tabIndex={0}
        className={classnames('dropdown', { open: collapse })}>
        {iconElement}
        <span>{label}</span>
      </a>
      <Collapse isOpen={collapse}>
        <ul>{renderedItems}</ul>
      </Collapse>
    </li>
  )
}

SidebarDropdown.propTypes = {
  items: PropTypes.array.isRequired,
  icon: PropTypes.oneOfType([
    PropTypes.bool,
    PropTypes.string,
  ]),
  label: PropTypes.string.isRequired
}

export default SidebarDropdown;
