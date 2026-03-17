import { useCallback } from 'react';
import { markChild } from '../api/api.js';
import ChildItem from './ChildItem.jsx';

export default function ChildList({ marks, type, loginToken, timeId, onMarksUpdate }) {
  const handleMark = useCallback(
    async (chidonId, checked) => {
      const result = await markChild(loginToken, timeId, chidonId, checked);
      if (!result.success) {
        // If the server rejected the mark, revert by refreshing marks
        console.error('Failed to mark child:', chidonId, result.error);
      }
    },
    [loginToken, timeId]
  );

  function checkAllInGroup(groupNumber) {
    const children = marks[groupNumber] || [];
    const allMarked = children.every((c) => c.marked);
    children.forEach((c) => {
      handleMark(c.th_chidon_id, !allMarked);
    });
  }

  const groupNumbers = Object.keys(marks).sort((a, b) => Number(a) - Number(b));

  if (groupNumbers.length === 0) {
    return (
      <div className="section-card">
        <div className="empty-state">No students found for the selected groups and time.</div>
      </div>
    );
  }

  return (
    <div className="child-list-container">
      {groupNumbers.map((groupNum) => {
        const children = marks[groupNum] || [];
        const markedCount = children.filter((c) => c.marked).length;

        return (
          <div key={groupNum} className="section-card group-section">
            <div className="group-section-header">
              <div className="group-section-title">
                {type} {groupNum}
                <span style={{ fontSize: '0.8rem', fontWeight: 400, color: '#636e72', marginLeft: 8 }}>
                  ({markedCount}/{children.length} marked)
                </span>
              </div>
              <button
                className="check-all-btn"
                onClick={() => checkAllInGroup(groupNum)}
              >
                Check / Uncheck All
              </button>
            </div>

            <div className="children-grid">
              {children.map((child) => (
                <ChildItem
                  key={child.th_chidon_id}
                  child={child}
                  type={type}
                  loginToken={loginToken}
                  timeId={timeId}
                  onMark={handleMark}
                />
              ))}
            </div>
          </div>
        );
      })}
    </div>
  );
}
