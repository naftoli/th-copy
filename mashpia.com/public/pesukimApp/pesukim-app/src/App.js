import React from "react";
import Sidebar from "./components/Sidebar";

import Home from "./pages/Home";
import About from "./pages/About";
import Join from "./pages/Join";
import Report from "./pages/Report";
import Learn from "./pages/Learn";
import Missions from "./pages/Missions";
import Prizes from "./pages/Prizes";
import WatchCampaign from "./pages/WatchCampaign";

import "./App.css";

function App() {
  return (
    <div className="App">
      <Sidebar />
      <div className="scroll-container" id="scrollContainer">
        <section className="page" id="page-0"><Home /></section>
        <section className="page" id="page-1"><About /></section>
        <section className="page" id="page-2"><Join /></section>
        <section className="page" id="page-3"><Report /></section>
        <section className="page" id="page-4"><Learn /></section>
        <section className="page" id="page-5"><Missions /></section>
        <section className="page" id="page-6"><Prizes /></section>
        <section className="page" id="page-7"><WatchCampaign /></section>
      </div>
    </div>
  );
}

export default App;
