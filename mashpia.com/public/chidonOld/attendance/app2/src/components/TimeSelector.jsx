export default function TimeSelector({ times, selectedTimeId, onChange }) {
  if (!times || times.length === 0) {
    return (
      <div className="section-card">
        <h3>Attendance Time</h3>
        <div className="empty-state">No attendance times available for these groups.</div>
      </div>
    );
  }

  return (
    <div className="section-card">
      <h3>Attendance Time</h3>
      <select
        className="time-select"
        value={selectedTimeId || ''}
        onChange={(e) => {
          const opt = e.target.options[e.target.selectedIndex];
          onChange(e.target.value, opt.dataset.type);
        }}
      >
        {times.map((t) => (
          <option key={t.key} value={t.key} data-type={t.type}>
            {t.time} : {t.type} — {t.description}
          </option>
        ))}
      </select>
    </div>
  );
}
