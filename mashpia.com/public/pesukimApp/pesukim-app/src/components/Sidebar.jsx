import React, { useEffect, useState } from "react";
import "./Sidebar.css";

const pages = [
  { name: "Home", id: "page-0" },
  { name: "About", id: "page-1" },
  { name: "Join", id: "page-2" },
  { name: "Report", id: "page-3" },
  { name: "Learn", id: "page-4" },
  { name: "Missions", id: "page-5" },
  { name: "Prizes", id: "page-6" },
  { name: "Watch The Campaign", id: "page-7" },
];

export default function Sidebar() {
  const [activeId, setActiveId] = useState("page-0");
  const [open, setOpen] = useState(false);

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
              className={p.id === activeId ? "active" : ""}
              onClick={() => scrollToId(p.id)}
            >
              {p.name}
            </li>
          ))}
        </ul>
      </aside>
    </>
  );
}
