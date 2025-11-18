import React from "react";
import { PrizeCard } from "./Prizes";

// Swap these with your actual images
import menorah from "../assets/images/menorah.png";
import becher from "../assets/images/becher.png";
import candlestick from "../assets/images/candlestick.png";
import gopro from "../assets/images/gopro-camera.jpg";
import printer3d from "../assets/images/3d-printer.png";
import scooter from "../assets/images/electric-scooter.jpg";
import bike from "../assets/images/electric-bike.jpg";


export default function Prizes() {
  return (
    <section className="prizes">
      <h1 className="title-text">AUCTION PRIZES</h1>

      <p className="body-text prizes-subtitle text-center">
        When you join and do missions you will earn miles that you can cash in to buy tickets
        in this year’s Tzivos Hashem Auction for a chance to win amazing prizes including:
      </p>

      {/* SILVER */}
      <h3 className="prize-category">SILVER</h3>
      <div className="prize-grid col-3">
        <PrizeCard
          title={"Sterling\nSilver Menorah"}
          img={menorah}
          alt="Sterling Silver Menorah"
        />
        <PrizeCard
          title={"Sterling\nSilver Becher"}
          img={becher}
          alt="Sterling Silver Becher"
        />
        <PrizeCard
          title={"Sterling\nSilver Candlestick"}
          img={candlestick}
          alt="Sterling Silver Candlestick"
        />
      </div>

      {/* ELECTRONICS */}
      <h3 className="prize-category">ELECTRONICS</h3>
      <div className="prize-grid col-4">
        <PrizeCard
          title={"GoPro\nCamera"}
          img={gopro}
          alt="GoPro Camera"
          width="100px"
        />
        <PrizeCard
          title={"3D\nPrinter"}
          img={printer3d}
          alt="3D Printer"
          width="100px"
        />
         <PrizeCard
          title={"Electric\nScooter"}
          img={scooter}
          alt="Electric Scooter"
          width="100px"
        />
         <PrizeCard
          title={"Electric\nBike"}
          img={bike}
          alt="Electric Bike"
          width="100px"
        />
      </div>
    </section>
  );
}
