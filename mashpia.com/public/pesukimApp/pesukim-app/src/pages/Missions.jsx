import React from "react";
import "./Missions.css";

import iconBook from "../assets/images/open-book.svg";
import iconSpeechBubble from "../assets/images/speech-bubble.svg";
import iconHand from "../assets/images/pointing-hand.svg";
import iconHandShake from "../assets/images/hand-shake.svg";

export default function Missions() {
  return (
    <section className="page missions">
      <h1 className="title-text">MISSIONS &amp; MILES</h1>

      {/* TOP ROW */}
      <div className="missions-grid top">
        <article className="card mission-card">
          <img src={iconBook} alt="" className="mission-icon" />
          <div className="mission-step-label">Step 1</div>
          <h3 className="mission-title">
            <span className="accent">LEARN</span> &nbsp;&amp; GET TESTED
          </h3>
          <p className="body-text mission-body">
            Learn the words, the Translation and explanation of each Possuk by
            heart. Once you’ve mastered them, get tested by an official tester,
            a parent or Teacher to confirm you know them well. You’ll earn up to
            <strong> 60 Auction Miles</strong> (5 miles per Possuk) for
            completing this step!
          </p>
        </article>

        <article className="card mission-card">
          <img src={iconSpeechBubble} alt="" className="mission-icon" />
          <div className="mission-step-label">Step 2</div>
          <h3 className="mission-title">
            SAY &nbsp;&amp; REPORT
          </h3>
          <p className="body-text mission-body">
            Say the 12 Pessukim wherever you go! And encourage others to say
            them too. For example: between one game and next you can say the 12
            Pessukim and remind your friends to say them. <strong>Report:</strong>{" "}
            Every day, log into your Tzivos Hashem account and report how many
            times you said them. Each time you say all 12, you earn 1 extra
            Auction Mile. Let’s see how many times a day Children around the
            world can say the 12 Pessukim.
          </p>
        </article>

        <article className="card mission-card">
          <img src={iconHand} alt="" className="mission-icon hand-icon" />
          <div className="mission-step-label">Step 3</div>
          <h3 className="mission-title">
            <span className="accent">TEACH</span> &nbsp;&amp; REPORT
          </h3>
          <p className="body-text mission-body">
            Become a teacher. Wherever you go, share your knowledge and teach
            people the 12 Pessukim. Every child is challenged to teach the 12
            Pessukim to at least one new person each month. Each person you
            teach the 12 Pessukim can earn you up to <strong>60 Auction Miles</strong>.
            (5 miles for each Possuk).
          </p>
        </article>
      </div>

      {/* BOTTOM ROW */}
      <div >
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
