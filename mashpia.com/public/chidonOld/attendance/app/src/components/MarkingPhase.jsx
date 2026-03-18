import React, { useState, useEffect, useCallback } from 'react';
import * as api from '../api';
import ChildItem from './ChildItem';

export default function MarkingPhase({ token, groups, onBack }) {
  const [timesState, setTimesState] = useState({ loading: true, times: [], error: null });
  const [selectedTime, setSelectedTime] = useState(null);
  const [childrenState, setChildrenState] = useState({
    loading: false,
    marks: {},
    type: '',
    error: null,
  });

  // Load available times when groups change
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

  // Load children when selected time changes
  useEffect(() => {
    if (!selectedTime) return;
    let cancelled = false;
    setChildrenState({ loading: true, marks: {}, type: '', error: null });

    api.getMarkingDetails(token, selectedTime.key, selectedTime.type, groups)
      .then(result => {
        if (cancelled) return;
        if (!result.success) {
          setChildrenState({
            loading: false,
            marks: {},
            type: '',
            error: result.error || 'Failed to load students.',
          });
          return;
        }
        setChildrenState({
          loading: false,
          marks: result.marks || {},
          type: result.type || selectedTime.type,
          error: null,
        });
      })
      .catch(() => {
        if (!cancelled) {
          setChildrenState({
            loading: false,
            marks: {},
            type: '',
            error: 'Connection error loading students.',
          });
        }
      });

    return () => { cancelled = true; };
  }, [token, selectedTime, groups]);

  // Toggle a single child with optimistic update + server revert on failure
  const handleToggleChild = useCallback((chidonId, groupNum, newMarked) => {
    const updateChild = (marked) => {
      setChildrenState(prev => {
        const groupChildren = (prev.marks[groupNum] || []).map(c =>
          c.th_chidon_id === chidonId ? Object.assign({}, c, { marked }) : c
        );
        return Object.assign({}, prev, {
          marks: Object.assign({}, prev.marks, { [groupNum]: groupChildren }),
        });
      });
    };

    updateChild(newMarked);

    api.markChild(token, selectedTime.key, chidonId, newMarked)
      .then(result => {
        if (!result.success) updateChild(!newMarked);
      })
      .catch(() => {
        updateChild(!newMarked);
      });
  }, [token, selectedTime]);

  // Toggle all children in a group
  const handleCheckAllGroup = useCallback((groupNum) => {
    const children = childrenState.marks[groupNum] || [];
    const allMarked = children.every(c => c.marked);
    children.forEach(c => {
      handleToggleChild(c.th_chidon_id, groupNum, !allMarked);
    });
  }, [childrenState.marks, handleToggleChild]);

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

      {/* Children list */}
      {!childrenState.loading && !childrenState.error && (
        <ChildrenList
          marks={childrenState.marks}
          type={childrenState.type}
          onToggle={handleToggleChild}
          onCheckAll={handleCheckAllGroup}
        />
      )}
    </>
  );
}

function ChildrenList({ marks, type, onToggle, onCheckAll }) {
  const groupNumbers = Object.keys(marks).sort((a, b) => Number(a) - Number(b));

  if (groupNumbers.length === 0) return null;

  return groupNumbers.map(gNum => {
    const children = marks[gNum] || [];
    const markedCount = children.filter(c => c.marked).length;

    return (
      <div className="section-card group-section" key={gNum}>
        <div className="group-section-header">
          <div className="group-section-title">
            {type} {gNum}
            <span className="marked-count">({markedCount}/{children.length} marked)</span>
          </div>
          <button className="check-all-btn" onClick={() => onCheckAll(gNum)}>
            Check / Uncheck All
          </button>
        </div>
        <div className="children-grid">
          {children.map(child => (
            <ChildItem
              key={child.th_chidon_id}
              child={child}
              type={type}
              onToggle={(newMarked) => onToggle(child.th_chidon_id, gNum, newMarked)}
            />
          ))}
        </div>
      </div>
    );
  });
}
