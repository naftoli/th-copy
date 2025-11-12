import React, { useEffect, useState } from "react";
import Sidebar from "./components/Sidebar";

import Home from "./pages/Home";
import About from "./pages/About";
import Join from "./pages/Join";
import Report from "./pages/Report";
import Learn from "./pages/Learn";
import Missions from "./pages/Missions";
import Prizes from "./pages/Prizes";
import WatchCampaign from "./pages/WatchCampaign";
import MenuTrackers from "./pages/MenuTrackers";

import "./App.css";

function App() {
  const [trackersData, setTrackersData] = useState(null);
  const [trackersError, setTrackersError] = useState("");

  useEffect(() => {
    let isMounted = true;

    async function load() {
      try {
        const res = await fetch("/api/pesukim/trackers.json", { cache: "no-store" });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (isMounted) setTrackersData(json);
      } catch (e) {
        if (isMounted) setTrackersError("Couldn't load trackers.");
      }
    }

    load();
    // if we want it to refresh periodically, uncomment:
    // const id = setInterval(load, 15000);
    return () => {
      isMounted = false;
      // clearInterval(id);
    };
  }, []);

  return (
    <div className="App">
      <Sidebar trackersData={trackersData} />
      <div className="scroll-container" id="scrollContainer">
        <section className="page" id="home"><Home /></section>
        <section className="page" id="menu-trackers">
          <MenuTrackers data={trackersData} error={trackersError} />
        </section>
        <section className="page" id="about"><About /></section>
        <section className="page" id="join"><Join /></section>
        <section className="page" id="report"><Report /></section>
        <section className="page" id="learn"><Learn /></section>
        <section className="page" id="missions"><Missions /></section>
        <section className="page" id="prizes"><Prizes /></section>
        <section className="page" id="watch-campaign"><WatchCampaign /></section>
      </div>
    </div>
  );
}

export default App;
