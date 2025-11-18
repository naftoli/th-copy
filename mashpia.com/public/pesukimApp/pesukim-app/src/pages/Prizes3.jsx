import React from "react";
import { PrizeCard } from "./Prizes";
import "./Prizes.css";

// Swap these with your actual images
import beisHamikdash from "../assets/images/rebbe.jpg";
import vrGlasses from "../assets/images/tvr-glasses.jpg";
import torahVR from "../assets/images/torah-vr.png";


export default function Prizes() {
  return (
    <section className="prizes">
      <h1 className="title-text">AUCTION PRIZES</h1>

      <p className="body-text prizes-subtitle text-center">
        When you join and do missions you will earn miles that you can cash in to buy tickets
        in this year’s Tzivos Hashem Auction for a chance to win amazing prizes including:
      </p>

      {/* BEIS HAMIKDASH */}
      <h3 className="prize-category">BEIS HAMIKDASH</h3>
      <div className="prize-grid col-2 mb-auto">
        <PrizeCard
          title={"Model\nBeis Hamikdash"}
          img={beisHamikdash}
          alt="Model Beis Hamikdash"
          width="100px"
        />
        <PrizeCard
          title={"VR Glasses\n+1 year subscription\nto TorahVR"}
          img={vrGlasses}
          img2={torahVR}
          width="90px"
          height="90px"
          alt="VR Glasses +1 year subscription to TorahVR"
        />
      </div>
    </section>
  );
}
