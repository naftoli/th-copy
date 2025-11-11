import React, { useEffect, useState } from "react";
import "./Sidebar.css";

const Sidebar = () => {
  const [activeId, setActiveId] = useState("page-0");

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

  // Scroll to section when clicked
  const handleClick = (id) => {
    const section = document.getElementById(id);
    if (section) {
      section.scrollIntoView({ behavior: "smooth" });
      window.location.hash = id; // update hash
    }
  };

  // Detect hash change or scroll
  useEffect(() => {
    const onHashChange = () => {
      const currentHash = window.location.hash.replace("#", "");
      if (currentHash) setActiveId(currentHash);
    };

    // Initialize with current hash or default
    onHashChange();

    window.addEventListener("hashchange", onHashChange);
    return () => window.removeEventListener("hashchange", onHashChange);
  }, []);

  // Detect section currently visible and update hash
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries.find((e) => e.isIntersecting);
        if (visible) {
          const id = visible.target.id;
          if (id !== activeId) {
            setActiveId(id);
            window.history.replaceState(null, "", `#${id}`);
          }
        }
      },
      { threshold: 0.5 }
    );

    document.querySelectorAll(".page").forEach((el) => observer.observe(el));
    return () => observer.disconnect();
  }, [activeId]);

  return (
    <aside className="sidebar">
      <ul>
        {pages.map((p) => (
          <li
            key={p.id}
            className={p.id === activeId ? "active" : ""}
            onClick={() => handleClick(p.id)}
          >
            {p.name}
          </li>
        ))}
      </ul>
    </aside>
  );
};

export default Sidebar;
