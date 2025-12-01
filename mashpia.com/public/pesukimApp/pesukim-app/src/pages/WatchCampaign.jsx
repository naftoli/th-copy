import React, { useEffect, useMemo, useRef, useState } from "react";
import "./WatchCampaign.css";
import TrackerBar from "../components/TrackerBar"; // <- same API as your example

// const pct = (n, d) => (d > 0 ? n / d : 0);

function chunkArray(arr, size) {
  const res = [];
  for (let i = 0; i < arr.length; i += size) res.push(arr.slice(i, i + size));
  return res;
}

export default function WatchCampaign() {
  const [mode, setMode] = useState("country"); // 'country' | 'school'
  const [data, setData]   = useState(null);
  const [err, setErr]     = useState("");
  const scrollAreaRef = useRef(null);

  const units = useMemo(() => {
    const rawUnits = (mode === "country" ? data?.countries : data?.schools) || [];
    // Sort by rank if rank exists, otherwise keep original order
    // Create a new array and sort it to preserve all properties
    const sorted = [...rawUnits];
    sorted.sort((a, b) => {
      const rankA = a.rank != null ? a.rank : Infinity;
      const rankB = b.rank != null ? b.rank : Infinity;
      return rankA - rankB;
    });
    return sorted;
  }, [mode, data]);

   // Totals for top trackers
   const totals = useMemo(() => {
    const learnCurrent   = units.reduce((s,u)=> s + (parseInt(u.learn?.current)||0), 0);
    const learnGoal      = units.reduce((s,u)=> s + (parseInt(u.learn?.goal)||0),    0);
    const recruitCurrent = units.reduce((s,u)=> s + (parseInt(u.recruit?.current)||0),0);
    const recruitGoal    = units.reduce((s,u)=> s + (parseInt(u.recruit?.goal)||0),   0);
    return { learnCurrent, learnGoal, recruitCurrent, recruitGoal };
  }, [units]);

  useEffect(() => {
    let on = true;
    (async () => {
      try {
        const res = await fetch("/api/pesukim/getCampaignInfo.php", { cache: "no-store" });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (on) setData(json.data);
      } catch (e) {
        if (on) setErr("Couldn't load campaign.");
      }
    })();
    return () => { on = false; };
  }, []);

  // Handle scroll snapping to previous section when at top
  useEffect(() => {
    // Wait for data to load so scroll area is rendered
    if (!data) return;

    const scrollArea = scrollAreaRef.current;
    if (!scrollArea) return;

    const handleWheel = (e) => {
      const { scrollTop } = scrollArea;
      const threshold = 10; // Small threshold to detect near boundaries
      const isAtTop = scrollTop <= threshold;
      const scrollingUp = e.deltaY < 0;

      // If at top and scrolling up, scroll to prizes section
      if (isAtTop && scrollingUp) {
        e.preventDefault();
        const prizesEl = document.getElementById("prizes");
        if (prizesEl) {
          prizesEl.scrollIntoView({ behavior: "smooth" });
          window.location.hash = "prizes";
        }
        return;
      }
    };

    scrollArea.addEventListener('wheel', handleWheel, { passive: false });

    return () => {
      scrollArea.removeEventListener('wheel', handleWheel);
    };
  }, [data]);

  if (err) return <div className="campaign-error card card-blue">{err}</div>;
  if (!data) return <div className="campaign-loading card card-blue">Loading…</div>;

  return (
    <section className="campaign">
      <div className="campaign-header">
        <h1 className="title-text">WATCH THE CAMPAIGN</h1>
        <div className="campaign-top-trackers">
          <div className="tracker-wrap">
            <TrackerBar value={totals.learnCurrent} tone="red" />
          </div>
          <div className="tracker-wrap">
            <TrackerBar value={totals.recruitCurrent} tone="yellow" />
          </div>
          <div className="sort-by-section">
            <span className="sort-label">SORT BY</span>
            <div className="sort-toggle" role="tablist" aria-label="Sort by">
              <button
                role="tab"
                aria-selected={mode === "country"}
                className={
                  "toggle toggle-left" + (mode === "country" ? " active" : "")
                }
                onClick={() => setMode("country")}
              >
                COUNTRY
              </button>
              <button
                role="tab"
                aria-selected={mode === "school"}
                className={
                  "toggle toggle-right" + (mode === "school" ? " active" : "")
                }
                onClick={() => setMode("school")}
              >
                SCHOOL
              </button>
            </div>
          </div>

        </div>
      </div>

      {/* scrollable snap sections */}
      <div className="campaign-scroll-area" ref={scrollAreaRef}>
        {chunkArray(units, 4).map((group, i) => (
          <div key={i} className="campaign-section">
            <div className="campaign-grid">
              {group.map((u) => (
                <article key={u.id} className="card card-blue campaign-card">
                  <div className="card-badge">
                    {u.logo && <img src={u.logo} alt="" />}
                  </div>
                  {u.rank && (
                    <div className="card-badge">
                      <div className="card-badge-rank-icon">{u.rank}</div>
                    </div>
                  )}
                  <div>
                  <h3 className="card-title">{u.name}</h3>
                  {u.subtitle && <p className="card-sub">{u.subtitle}</p>}
                  </div>
                  <div className="card-section red">
                    <p className="mini-label">Chanukah Teaching Goal:</p>
                    <p className="mini-value">{u.learn.goal.toLocaleString()}</p>
                    <p className="mini-label">People Taught:</p>
                    <p className="mini-value">{u.learn.current.toLocaleString()}</p>
                    <TrackerBar value={u.learn.current} max={u.learn.goal} tone="red" />
                  </div>
                  <div className="card-section yellow">
                    <p className="mini-label">Chanukah Recruitment Goal:</p>
                    <p className="mini-value">{u.recruit.goal.toLocaleString()}</p>
                    <p className="mini-label">Children Recruited:</p>
                    <p className="mini-value">{u.recruit.current.toLocaleString()}</p>
                    <TrackerBar value={u.recruit.current} max={u.recruit.goal} tone="yellow" />
                  </div>
                  <div className="card-footer">
                    <p className="footer-title">Total Times<br/>Pessukim Said:</p>
                    <p className="footer-value">{u.pesukimTotal.toLocaleString()}</p>
                  </div>
                </article>
              ))}
            </div>
          </div>
        ))}
      </div>
    </section>

  );
}
