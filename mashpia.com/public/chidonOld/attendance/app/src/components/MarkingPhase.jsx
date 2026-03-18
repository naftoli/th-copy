import React, { useState, useEffect, useCallback, useMemo } from 'react';
import * as api from '../api';
import ChildItem from './ChildItem';

// Build a map of groupValue → typeName from info.assignments
function buildGroupTypeMap(info) {
  const assignments = (info && info.assignments) || {};
  const map = {};
  Object.entries(assignments).forEach(([typeName, groupsObj]) => {
    if (typeName === 'counselors' || typeName === 'walking_counselors') return;
    Object.values(groupsObj).filter(Boolean).forEach(g => {
      map[g] = typeName;
    });
  });
  return map;
}

export default function MarkingPhase({ token, groups, info, onBack }) {
  const groupTypeMap = useMemo(() => buildGroupTypeMap(info), [info]);

  // Split selected groups by their type: { typeName: [group, ...] }
  const groupsByType = useMemo(() => {
    const result = {};
    groups.forEach(g => {
      const type = groupTypeMap[g];
      if (type) {
        if (!result[type]) result[type] = [];
        result[type].push(g);
      }
    });
    return result;
  }, [groups, groupTypeMap]);

  const [timesState, setTimesState] = useState({ loading: true, times: [], error: null });
  const [selectedTime, setSelectedTime] = useState(null);

  // sections: [{ sectionKey, type, groupNum, children }]
  // Using sectionKey = `${type}-${groupNum}` to handle same group number across types
  const [childrenState, setChildrenState] = useState({
    loading: false,
    sections: [],
    error: null,
  });

  // Load available times
  useEffect(() => {
    let cancelled = false;
    setTimesState({ loading: true, times: [], error: null });

    api.getMarkingOptions(token, groups)
      .then(result => {
        if (cancelled) return;
        if (!result.success) {
          setTimesState({ loading: false, times: [], error: result.error || 'Failed to load times.' });
          return;
        }
        const times = result.times || [];
        setTimesState({ loading: false, times, error: null });
        if (times.length > 0) setSelectedTime(times[0]);
      })
      .catch(() => {
        if (!cancelled) {
          setTimesState({ loading: false, times: [], error: 'Connection error loading times.' });
        }
      });

    return () => { cancelled = true; };
  }, [token, groups]);

  // Load children for ALL selected group types when the selected time changes.
  // One API call per type so the backend can query each type's table correctly.
  useEffect(() => {
    if (!selectedTime) return;
    let cancelled = false;
    setChildrenState({ loading: true, sections: [], error: null });

    const calls = Object.entries(groupsByType).map(([type, typeGroups]) =>
      api.getMarkingDetails(token, selectedTime.key, type, typeGroups)
        .then(result => ({ type, result }))
    );

    Promise.all(calls)
      .then(responses => {
        if (cancelled) return;
        const sections = [];
        let hasError = false;

        responses.forEach(({ type, result }) => {
          if (!result.success) { hasError = true; return; }
          const marks = result.marks || {};
          const resolvedType = result.type || type;
          Object.keys(marks)
            .sort((a, b) => Number(a) - Number(b))
            .forEach(groupNum => {
              sections.push({
                sectionKey: `${resolvedType}-${groupNum}`,
                type: resolvedType,
                groupNum,
                children: marks[groupNum] || [],
              });
            });
        });

        if (hasError && sections.length === 0) {
          setChildrenState({ loading: false, sections: [], error: 'Failed to load students.' });
        } else {
          setChildrenState({ loading: false, sections, error: null });
        }
      })
      .catch(() => {
        if (!cancelled) {
          setChildrenState({ loading: false, sections: [], error: 'Connection error loading students.' });
        }
      });

    return () => { cancelled = true; };
  }, [token, selectedTime, groupsByType]);

  // Optimistic toggle with server revert on failure
  const handleToggleChild = useCallback((chidonId, sectionKey, newMarked) => {
    const updateChild = (marked) => {
      setChildrenState(prev => ({
        ...prev,
        sections: prev.sections.map(section => {
          if (section.sectionKey !== sectionKey) return section;
          return {
            ...section,
            children: section.children.map(c =>
              c.th_chidon_id === chidonId ? Object.assign({}, c, { marked }) : c
            ),
          };
        }),
      }));
    };

    updateChild(newMarked);

    api.markChild(token, selectedTime.key, chidonId, newMarked)
      .then(result => { if (!result.success) updateChild(!newMarked); })
      .catch(() => updateChild(!newMarked));
  }, [token, selectedTime]);

  // Toggle all children in a section
  const handleCheckAllSection = useCallback((sectionKey) => {
    const section = childrenState.sections.find(s => s.sectionKey === sectionKey);
    if (!section) return;
    const allMarked = section.children.every(c => c.marked);
    section.children.forEach(c => {
      handleToggleChild(c.th_chidon_id, sectionKey, !allMarked);
    });
  }, [childrenState.sections, handleToggleChild]);

  return (
    <>
      {/* Back bar */}
      <div className="section-card back-bar">
        <button className="toggle-all-btn" onClick={onBack}>
          &larr; Back to Groups
        </button>
        <span className="groups-label">Groups: {groups.join(', ')}</span>
      </div>

      {/* Times loading / error / empty */}
      {timesState.loading && (
        <div className="loading-spinner">
          <div className="spinner" /> Loading times&hellip;
        </div>
      )}
      {timesState.error && (
        <div className="section-card">
          <div className="error-message">{timesState.error}</div>
        </div>
      )}
      {!timesState.loading && !timesState.error && timesState.times.length === 0 && (
        <div className="section-card">
          <div className="empty-state">No attendance times available for these groups.</div>
        </div>
      )}

      {/* Time selector */}
      {timesState.times.length > 0 && (
        <div className="section-card">
          <h3>Attendance Time</h3>
          <select
            className="time-select"
            value={selectedTime ? selectedTime.key : ''}
            onChange={e => {
              const found = timesState.times.find(t => t.key === e.target.value);
              if (found) setSelectedTime(found);
            }}
          >
            {timesState.times.map(t => (
              <option key={t.key} value={t.key}>
                {t.time} : {t.type} &mdash; {t.description}
              </option>
            ))}
          </select>
        </div>
      )}

      {/* Children loading / error */}
      {childrenState.loading && (
        <div className="loading-spinner">
          <div className="spinner" /> Loading students&hellip;
        </div>
      )}
      {childrenState.error && (
        <div className="section-card">
          <div className="error-message">{childrenState.error}</div>
        </div>
      )}

      {/* Children sections */}
      {!childrenState.loading && !childrenState.error && (
        <ChildrenList
          sections={childrenState.sections}
          onToggle={handleToggleChild}
          onCheckAll={handleCheckAllSection}
        />
      )}
    </>
  );
}

function ChildrenList({ sections, onToggle, onCheckAll }) {
  if (sections.length === 0) return null;

  return sections.map(({ sectionKey, type, groupNum, children }) => {
    const markedCount = children.filter(c => c.marked).length;
    return (
      <div className="section-card group-section" key={sectionKey}>
        <div className="group-section-header">
          <div className="group-section-title">
            {type} {groupNum}
            <span className="marked-count">({markedCount}/{children.length} marked)</span>
          </div>
          <button className="check-all-btn" onClick={() => onCheckAll(sectionKey)}>
            Check / Uncheck All
          </button>
        </div>
        <div className="children-grid">
          {children.map(child => (
            <ChildItem
              key={child.th_chidon_id}
              child={child}
              type={type}
              onToggle={(newMarked) => onToggle(child.th_chidon_id, sectionKey, newMarked)}
            />
          ))}
        </div>
      </div>
    );
  });
}
