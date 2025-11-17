import React, { useEffect, useState } from "react";
import TrackerBar from "./TrackerBar";
import "./Sidebar.css";

import logo from "../assets/images/tzivos-hashem-logo.png";

const pages = [
  { name: "Home", id: "home", subIds: ["menu-trackers"] },
  { name: "About", id: "about", subIds: [] },
  { name: "Join", id: "join", subIds: [] },
  { name: "Report", id: "report", subIds: [] },
  { name: "Learn", id: "learn", subIds: [] },
  { name: "Missions", id: "missions", subIds: ["missions-page-2"] },
  { name: "Prizes", id: "prizes", subIds: [] },
  { name: "Watch The Campaign", id: "watch-campaign", subIds: [] },
];

export default function Sidebar({ trackersData = {} }) {
  const [activeId, setActiveId] = useState("home");
  const [open, setOpen] = useState(false);
  
  const { learnTeach = {}, armyRecruitment = {} } = trackersData || {};
  
  const scrollToId = (id) => {
    const el = document.getElementById(id);
    if (el) {
      el.scrollIntoView({ behavior: "smooth" });
      window.location.hash = id;
      setOpen(false); // close drawer on mobile after click
    }
  };

  // hash -> active
  useEffect(() => {
    const onHashChange = () => {
      const id = window.location.hash.replace("#", "");
      if (id) setActiveId(id);
    };
    onHashChange();
    window.addEventListener("hashchange", onHashChange);
    return () => window.removeEventListener("hashchange", onHashChange);
  }, []);

  // visible section -> active/hash
  useEffect(() => {
    const obs = new IntersectionObserver(
      (entries) => {
        const entry = entries
          .filter((e) => e.isIntersecting)
          .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (entry && entry.target.id !== activeId) {
          setActiveId(entry.target.id);
          window.history.replaceState(null, "", `#${entry.target.id}`);
        }
      },
      { root: document.getElementById("scrollContainer"), threshold: 0.55 }
    );
    document.querySelectorAll(".page").forEach((el) => obs.observe(el));
    return () => obs.disconnect();
  }, [activeId]);

  // close on ESC (mobile)
  useEffect(() => {
    const onKey = (e) => e.key === "Escape" && setOpen(false);
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  return (
    <>
      {/* Mobile toggle button */}
      <button
        className="menu-toggle"
        type="button"
        aria-label="Open menu"
        aria-expanded={open}
        onClick={() => setOpen((o) => !o)}
      >
        ☰
      </button>

      {/* Backdrop for mobile */}
      <div
        className={`backdrop ${open ? "show" : ""}`}
        onClick={() => setOpen(false)}
        aria-hidden="true"
      />

      <aside className={`sidebar ${open ? "open" : ""}`}>
        <ul>
          {pages.map((p) => (
            <li
              key={p.id}
              className={p.id === activeId || p.subIds.includes(activeId) ? "active" : ""}
              onClick={() => scrollToId(p.id)}
            >
              {p.name}
            </li>
          ))}
        </ul>

        {/* Trackers */}
        <div className="trackers">
          <TrackerBar
            value={learnTeach.taught}
            max={learnTeach.goal}
            size="small"
            height={18}
            tone="red"         // red bar + red bubble
          />

          <TrackerBar
            value={armyRecruitment.recruited}
            max={armyRecruitment.goal}
            size="small"
            height={18}
            tone="yellow"      // yellow bar + yellow bubble
          />
        </div>

        <img src={logo} alt="Tzivos Hashem logo" className="sidebar-logo" />

      </aside>
    </>
  );
}
