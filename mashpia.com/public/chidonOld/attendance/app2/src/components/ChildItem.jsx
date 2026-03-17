import { useOptimistic, useTransition } from 'react';

export default function ChildItem({ child, type, loginToken, timeId, onMark }) {
  const [optimisticMarked, setOptimisticMarked] = useOptimistic(!!child.marked);
  const [, startTransition] = useTransition();

  function handleClick() {
    const newValue = !optimisticMarked;
    startTransition(async () => {
      setOptimisticMarked(newValue);
      await onMark(child.th_chidon_id, newValue);
    });
  }

  return (
    <div
      className={`child-item ${optimisticMarked ? 'marked' : ''}`}
      onClick={handleClick}
      role="checkbox"
      aria-checked={optimisticMarked}
      tabIndex={0}
      onKeyDown={(e) => e.key === ' ' || e.key === 'Enter' ? handleClick() : null}
    >
      <input
        type="checkbox"
        className="child-checkbox"
        checked={optimisticMarked}
        onChange={() => {}}
        aria-hidden="true"
      />
      <div className="child-check-icon">
        {optimisticMarked && <i className="fa fa-check" />}
      </div>

      <div className="child-info">
        <div className="child-name">
          {child.first} {child.last}
        </div>
        <div className="child-detail">
          School: {child.school_name}
          {type === 'walk' && child.dropoff_number != null && (
            <><br />Dropoff #: {child.dropoff_number}</>
          )}
        </div>

        {type === 'walk' && child.host && (
          <div className="child-host-info">
            <div>Host Family: {child.host}</div>
            <div className="child-host-address">
              {[
                child.host_street_num,
                child.host_street_num_suffix,
                child.host_street,
                child.host_street_apt,
              ]
                .filter(Boolean)
                .join(' ')}
            </div>
            {(child.between_streets1 || child.between_streets2) && (
              <div>
                Between {child.between_streets1} and {child.between_streets2}
              </div>
            )}
            {child.host_number && <div>Host Phone: {child.host_number}</div>}
          </div>
        )}

        {type === 'door' && (
          <div className="child-host-info">
            {child.host && (
              <div>
                Host Family: {child.host}
                {child.host_number && ` — ${child.host_number}`}
              </div>
            )}
            {child.chap_name && (
              <div>
                Walking Chaperone: {child.chap_name}
                {child.chap_phone && ` — ${child.chap_phone}`}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
