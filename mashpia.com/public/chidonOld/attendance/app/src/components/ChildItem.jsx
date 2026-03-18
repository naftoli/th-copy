import React from 'react';

export default function ChildItem({ child, type, onToggle }) {
  const marked = !!child.marked;

  const handleKeyDown = (e) => {
    if (e.key === ' ' || e.key === 'Enter') {
      e.preventDefault();
      onToggle(!marked);
    }
  };

  const addr = type === 'walk'
    ? [
        child.host_street_num,
        child.host_street_num_suffix,
        child.host_street,
        child.host_street_apt,
      ].filter(Boolean).join(' ')
    : '';

  return (
    <div
      className={`child-item${marked ? ' marked' : ''}`}
      role="checkbox"
      aria-checked={marked}
      tabIndex={0}
      onClick={() => onToggle(!marked)}
      onKeyDown={handleKeyDown}
    >
      <div className="child-check-icon">
        {marked && <i className="fa fa-check" aria-hidden="true" />}
      </div>
      <div className="child-info">
        <div className="child-name">{child.first} {child.last}</div>
        <div className="child-detail">
          School: {child.school_name}
          {type === 'walk' && child.dropoff_number != null && (
            <><br />Dropoff #: {child.dropoff_number}</>
          )}
        </div>

        {type === 'walk' && (
          <div className="child-host-info">
            {child.host && <div>Host Family: {child.host}</div>}
            {addr && <div className="child-host-address">{addr}</div>}
            {(child.between_streets1 || child.between_streets2) && (
              <div>Between {child.between_streets1} and {child.between_streets2}</div>
            )}
            {child.host_number && <div>Host Phone: {child.host_number}</div>}
          </div>
        )}

        {type === 'door' && (
          <div className="child-host-info">
            {child.host && (
              <div>
                Host Family: {child.host}
                {child.host_number ? ` \u2014 ${child.host_number}` : ''}
              </div>
            )}
            {child.chap_name && (
              <div>
                Walking Chaperone: {child.chap_name}
                {child.chap_phone ? ` \u2014 ${child.chap_phone}` : ''}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
