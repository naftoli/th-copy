import { useState, useCallback } from 'react';

function GroupCheckbox({ groupNumber, counselors, checked, onChange }) {
  const counselorNames = counselors?.[groupNumber]?.join(', ') || null;

  return (
    <label className={`group-checkbox-label ${checked ? 'checked' : ''}`}>
      <input
        type="checkbox"
        checked={checked}
        onChange={(e) => onChange(groupNumber, e.target.checked)}
      />
      <span>{groupNumber}</span>
      {counselorNames && (
        <span className="group-counselor">({counselorNames})</span>
      )}
    </label>
  );
}

export default function GroupSelector({ info, onSubmit }) {
  const [selected, setSelected] = useState(new Set());

  const assignments = info?.assignments || {};
  const counselors = assignments.counselors || {};
  const walkingCounselors = assignments.walking_counselors || {};

  const groupTypes = Object.entries(assignments).filter(
    ([key]) => key !== 'counselors' && key !== 'walking_counselors'
  );

  const handleChange = useCallback((groupNumber, checked) => {
    setSelected((prev) => {
      const next = new Set(prev);
      if (checked) {
        next.add(String(groupNumber));
      } else {
        next.delete(String(groupNumber));
      }
      return next;
    });
  }, []);

  function toggleAll(groups) {
    const groupNums = Object.values(groups).map(String);
    const allSelected = groupNums.every((g) => selected.has(g));
    setSelected((prev) => {
      const next = new Set(prev);
      if (allSelected) {
        groupNums.forEach((g) => next.delete(g));
      } else {
        groupNums.forEach((g) => next.add(g));
      }
      return next;
    });
  }

  function handleGo() {
    onSubmit(Array.from(selected));
  }

  return (
    <div>
      {groupTypes.map(([typeName, groups]) => {
        const isBunk = typeName === 'bunk';
        const counselorMap = isBunk ? counselors : walkingCounselors;
        const groupValues = Object.values(groups).filter(Boolean);
        const label = isBunk ? 'Bunks' : 'Walking Groups';

        return (
          <div key={typeName} className="section-card group-type-section">
            <h3>
              {label}
              <button
                type="button"
                className="toggle-all-btn"
                onClick={() => toggleAll(groups)}
                style={{ marginLeft: 12 }}
              >
                Toggle All
              </button>
            </h3>
            <div className="groups-grid">
              {groupValues.map((groupNum) => (
                <GroupCheckbox
                  key={groupNum}
                  groupNumber={groupNum}
                  counselors={counselorMap}
                  checked={selected.has(String(groupNum))}
                  onChange={handleChange}
                />
              ))}
            </div>
          </div>
        );
      })}

      <div className="section-card" style={{ textAlign: 'center' }}>
        <button
          className="go-button"
          disabled={selected.size === 0}
          onClick={handleGo}
        >
          Go to Marking ({selected.size} group{selected.size !== 1 ? 's' : ''} selected)
        </button>
      </div>
    </div>
  );
}
