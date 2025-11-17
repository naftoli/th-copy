import React from "react";
import "./MenuTrackers.css";
import TrackerBar from "../components/TrackerBar"; 

const fmt = (n) => Number(n || 0).toLocaleString();

export default function MenuTrackers({ data = {}, error }) {
  if (error) {
    return (
      <div className="mt-wrap">
        <div className="card card-blue">{error}</div>
      </div>
    );
  }

  if (!data) {
    return (
      <div className="mt-wrap">
        <div className="card card-blue">Loading…</div>
      </div>
    );
  }

  const { learnTeach = {}, armyRecruitment = {}, pesukim = {} } = data || {};

  return (
    <div className="mt-wrap">
      <section className="mt-row">
        <article className="card card-blue">
          <h2 className="title-text">LEARN &amp; TEACH</h2>
          <p className="body-text text-center">
            Teaching Goal:<br />
            Teach 50,000 Yidden<br />
            the 12 Pessukim
          </p>

          <TrackerBar
            value={learnTeach.taught}
            max={learnTeach.goal}
            tone="red"         // red bar + red bubble
          />

          <p className="body-text text-center">How many people can you teach?</p>
        </article>

        <article className="card card-blue">
          <h2 className="title-text">ARMY RECRUITMENT</h2>
          <p className="body-text text-center">
            Recruitment Goal:<br />
            Recruit 50,000 Children<br />
            to Hashem’s Army
          </p>

          <TrackerBar
            value={armyRecruitment.recruited}
            max={armyRecruitment.goal}
            tone="yellow"      // yellow bar + yellow bubble
          />

          <p className="body-text text-center">How many children can you recruit?</p>
        </article>
      </section>

      <section className="card card-navy">
          <h2 className="title-text">PESSUKIM <br />SAID</h2>
          <div className="mt-number">
            <div className="mt-date">
              {pesukim.date.dow}, {pesukim.date.hebrew} — {pesukim.date.gregorian}
            </div>
            <div className="mt-number-label">Today:</div>
            <div className="mt-number-value">{fmt(pesukim.today)} TIMES</div>
          </div>
          <div className="mt-number">
            <div className="mt-date"></div>
            <div className="mt-number-label">Total:</div>
            <div className="mt-number-value">{fmt(pesukim.total)} TIMES</div>
          </div>

      </section>
        <div className="mt-actions">
          <a className="btn secondary" href="#watch-campaign">
            WATCH THE CAMPAIGN
          </a>
        </div>
    </div>
  );
}
