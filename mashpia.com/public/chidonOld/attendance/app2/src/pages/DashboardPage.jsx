import { useState, useEffect, useCallback } from 'react';
import { getMarkingOptions, getMarkingDetails } from '../api/api.js';
import GroupSelector from '../components/GroupSelector.jsx';
import TimeSelector from '../components/TimeSelector.jsx';
import ChildList from '../components/ChildList.jsx';

const PHASE_GROUPS = 'groups';
const PHASE_MARKING = 'marking';

export default function DashboardPage({ loginToken, user, info, onLogout }) {
  const [phase, setPhase] = useState(PHASE_GROUPS);
  const [selectedGroups, setSelectedGroups] = useState([]);

  // Times
  const [times, setTimes] = useState([]);
  const [timesLoading, setTimesLoading] = useState(false);
  const [selectedTimeId, setSelectedTimeId] = useState(null);
  const [selectedTimeType, setSelectedTimeType] = useState(null);

  // Children
  const [marks, setMarks] = useState({});
  const [marksType, setMarksType] = useState('');
  const [marksLoading, setMarksLoading] = useState(false);
  const [marksError, setMarksError] = useState('');

  const roles = info?.roles?.join(' / ') || '';

  // When times load, auto-select the first
  useEffect(() => {
    if (times.length > 0 && !selectedTimeId) {
      setSelectedTimeId(times[0].key);
      setSelectedTimeType(times[0].type);
    }
  }, [times]);

  // When selected time changes, fetch children
  useEffect(() => {
    if (!selectedTimeId || !selectedTimeType || selectedGroups.length === 0) return;
    fetchChildren(selectedTimeId, selectedTimeType);
  }, [selectedTimeId, selectedTimeType]);

  async function handleGroupsSubmit(groups) {
    setSelectedGroups(groups);
    setPhase(PHASE_MARKING);
    setTimesLoading(true);
    setTimes([]);
    setSelectedTimeId(null);
    setSelectedTimeType(null);
    setMarks({});

    try {
      const result = await getMarkingOptions(loginToken, groups);
      if (result.success) {
        setTimes(result.times || []);
      } else {
        alert(result.error || 'Failed to load times.');
        setPhase(PHASE_GROUPS);
      }
    } catch {
      alert('Connection error loading times.');
      setPhase(PHASE_GROUPS);
    } finally {
      setTimesLoading(false);
    }
  }

  const fetchChildren = useCallback(
    async (timeId, type) => {
      setMarksLoading(true);
      setMarksError('');
      try {
        const result = await getMarkingDetails(loginToken, timeId, type, selectedGroups);
        if (result.success) {
          setMarks(result.marks || {});
          setMarksType(result.type || type);
        } else {
          setMarksError(result.error || 'Failed to load students.');
        }
      } catch {
        setMarksError('Connection error loading students.');
      } finally {
        setMarksLoading(false);
      }
    },
    [loginToken, selectedGroups]
  );

  function handleTimeChange(timeId, type) {
    setSelectedTimeId(timeId);
    setSelectedTimeType(type);
  }

  function handleBackToGroups() {
    setPhase(PHASE_GROUPS);
    setSelectedGroups([]);
    setTimes([]);
    setSelectedTimeId(null);
    setSelectedTimeType(null);
    setMarks({});
  }

  return (
    <div>
      <header className="app-header">
        <div>
          <h1>Chidon Attendance</h1>
        </div>
        <div className="user-name">
          {user.first_name} {user.last_name}
          {roles && <span style={{ opacity: 0.75 }}> ({roles})</span>}
        </div>
        <button className="btn-logout" onClick={onLogout}>
          Logout
        </button>
      </header>

      <div className="dashboard">
        {phase === PHASE_GROUPS && (
          <GroupSelector info={info} onSubmit={handleGroupsSubmit} />
        )}

        {phase === PHASE_MARKING && (
          <>
            <div className="section-card" style={{ display: 'flex', alignItems: 'center', gap: 12, padding: '12px 20px' }}>
              <button
                className="toggle-all-btn"
                onClick={handleBackToGroups}
                style={{ fontSize: '0.9rem', padding: '6px 16px' }}
              >
                ← Back to Groups
              </button>
              <span style={{ color: '#636e72', fontSize: '0.9rem' }}>
                Groups: {selectedGroups.join(', ')}
              </span>
            </div>

            {timesLoading ? (
              <div className="loading-spinner">
                <div className="spinner" /> Loading times…
              </div>
            ) : (
              <TimeSelector
                times={times}
                selectedTimeId={selectedTimeId}
                onChange={handleTimeChange}
              />
            )}

            {!timesLoading && times.length > 0 && (
              <>
                {marksLoading ? (
                  <div className="loading-spinner">
                    <div className="spinner" /> Loading students…
                  </div>
                ) : marksError ? (
                  <div className="section-card">
                    <div className="error-message">{marksError}</div>
                  </div>
                ) : (
                  <ChildList
                    marks={marks}
                    type={marksType}
                    loginToken={loginToken}
                    timeId={selectedTimeId}
                  />
                )}
              </>
            )}
          </>
        )}
      </div>
    </div>
  );
}
