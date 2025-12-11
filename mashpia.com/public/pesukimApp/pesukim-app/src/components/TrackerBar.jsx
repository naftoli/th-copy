import React, { useMemo } from "react";
import "./TrackerBar.css";

const fmt = (n) => Number(n || 0).toLocaleString();

export default function TrackerBar({
  size = "large", // "large" | "small"
  value = 0,
  max = 50000,
  tone = "red", // "red" | "yellow"
  height = 32,  // pill height
  className = "",
}) {
  const pct = useMemo(() => {
    if (!max || max <= 0) return 0;
    return Math.max(0, Math.min(100, (value / max) * 100));
  }, [value, max]);

  // keep bubble nicely centered over the inner fill edge
  const bubbleLeft = `${pct}%`;
  // const bubbleLeft = `calc(${pct}% - 8px)`;

  return (
    <div
      className={`tracker v2 ${tone} ${size} ${className}`}
      style={{ "--h": `${height}px` }}
      aria-valuemin={0}
      aria-valuemax={max}
      aria-valuenow={value}
      role="progressbar"
    >
      {/* outer track with vertical navy->tone gradient */}
      <div className="trk-track">
        {/* inner gutter provides the “air” around the fill */}
        <div className="trk-gutter">
          <div className="trk-fill" style={{ width: `${pct}%` }} />
        </div>
      </div>

      {/* right-side max label only */}
      <div className="trk-max">{fmt(max)}</div>

      {/* value bubble */}
      <div className="trk-bubble" style={{ left: bubbleLeft }}>
        {fmt(value)}
      </div>
    </div>
  );
}
