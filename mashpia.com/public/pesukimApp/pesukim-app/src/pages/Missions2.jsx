import React from "react";
import "./Missions.css";
import iconHandShake from "../assets/images/hand-shake.svg";

// THIS PAGE IS FOR THE MOBILE VERSION OF THE MISSIONS PAGE THAT SHOWS THE BOTTOM ROW OF CARDS

export default function Missions() {
  return (
    <section className="page missions">
      <h1 className="title-text">MISSIONS &amp; MILES</h1>
      {/* BOTTOM ROW */}
      <div>
        <div className="missions-grid bottom">
          {/* the handshake sits between these two via absolute positioning */}
          <img src={iconHandShake} alt="" className="between-icon" />

          <article className="card mission-card wide">
            <div className="mission-title single">
              <span className="accent">RECRUIT</span>
            </div>
            <p className="body-text mission-body">
              Get another child to join Tzivos Hashem and learn the 12 Pessukim by
              heart. You’ll earn up to <strong>600 Auction Miles</strong> for
              every new recruit who joins and learns all 12 Pessukim by heart.
              Just make sure they add your serial number so you get the miles you
              deserve.
            </p>
          </article>

          <article className="card mission-card wide">
            <div className="mission-title single">BONUS</div>
            <p className="body-text mission-body">
              Encourage your recruit to recruit their friends too! For every new
              child that your recruit brings into Tzivos Hashem, you’ll earn up to
              <strong> 60 Auction Miles</strong>! That means the more your recruits
              grow — the more your miles grow!
            </p>
          </article>
        </div>
      </div>
    </section>
  );
}
