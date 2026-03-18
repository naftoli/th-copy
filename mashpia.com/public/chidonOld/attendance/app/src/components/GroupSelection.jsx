import React, { useState, useMemo } from 'react';

export default function GroupSelection({ info, onGoToMarking }) {
  const assignments = (info && info.assignments) || {};
  const counselors = assignments.counselors || {};
  const walkingCounselors = assignments.walking_counselors || {};

  const groups = useMemo(() => {
    return Object.entries(assignments)
      .filter(([key]) => key !== 'counselors' && key !== 'walking_counselors')
      .map(([typeName, groupsObj]) => ({
        typeName,
        label: typeName === 'bunk' ? 'Bunks' : 'Walking Groups',
        values: Object.values(groupsObj).filter(Boolean),
        counselorMap: typeName === 'bunk' ? counselors : walkingCounselors,
      }));
  }, [assignments, counselors, walkingCounselors]);

  const [selected, setSelected] = useState(new Set());

  const toggle = (group) => {
    setSelected(prev => {
      const next = new Set(prev);
      if (next.has(group)) next.delete(group);
      else next.add(group);
      return next;
    });
  };

  const toggleAll = (values) => {
    setSelected(prev => {
      const allChecked = values.every(g => prev.has(g));
      const next = new Set(prev);
      if (allChecked) values.forEach(g => next.delete(g));
      else values.forEach(g => next.add(g));
      return next;
    });
  };

  const count = selected.size;

  return (
    <>
      {groups.map(({ typeName, label, values, counselorMap }) => (
        <div className="section-card" key={typeName}>
          <h3>
            {label}
            <button
              className="toggle-all-btn"
              onClick={() => toggleAll(values)}
            >
              Toggle All
            </button>
          </h3>
          <div className="groups-grid">
            {values.map(g => {
              const counselorNames = counselorMap[g]
                ? counselorMap[g].join(', ')
                : '';
              const isChecked = selected.has(g);
              return (
                <label
                  key={g}
                  className={`group-checkbox-label${isChecked ? ' checked' : ''}`}
                >
                  <input
                    type="checkbox"
                    checked={isChecked}
                    onChange={() => toggle(g)}
                  />
                  <span>{g}</span>
                  {counselorNames && (
                    <span className="group-counselor">({counselorNames})</span>
                  )}
                </label>
              );
            })}
          </div>
        </div>
      ))}

      <div className="section-card" style={{ textAlign: 'center' }}>
        <button
          className="go-button"
          disabled={count === 0}
          onClick={() => onGoToMarking(Array.from(selected))}
        >
          Go to Marking ({count} group{count !== 1 ? 's' : ''} selected)
        </button>
      </div>
    </>
  );
}
