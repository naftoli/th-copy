import React from "react";
import "./Home.css"; // Replace with PNG import after conversion
import logo from "../assets/images/tzivos-hashem-logo.png";
import boy from "../assets/images/boy-with-hat.png";
import girl from "../assets/images/girl-with-hat.png";
import HashemsArmyText from "../assets/images/hashems-army.svg";

// Note: TIF files are not supported by browsers. Convert to PNG first.
// import bgPattern from "../assets/images/monoline-illustration-bitmap.tif";
// For now, using a placeholder - replace with PNG version after conversion
const bgPattern = null;

const Home = () => {
  return (
    <div className="home-container">
      {bgPattern && (
        <div 
          className="home-background-pattern"
          style={{ 
            backgroundImage: `url(${bgPattern})`,
            backgroundRepeat: 'repeat',
            backgroundSize: 'auto'
          }}
        />
      )}

      <div className="home-top">
        <img src={girl} alt="Girl soldier" className="character" />
        <img src={logo} alt="Tzivos Hashem logo" className="home-logo" />
        <img src={boy} alt="Boy soldier" className="character" />
      </div>

      <div className="home-text">
        <h1 className="welcome">
          Welcome to
        </h1>

        
        <img src={HashemsArmyText} alt="Hashem's Army Text" className="hashems-army-text" />

        <h3 className="campaign-text">
          12 TORAH PASSAGES CAMPAIGN
        </h3>
        <h4 className="soldiers">
          — FOR YOUNG SOLDIERS —
        </h4>
      </div>

      <div className="home-buttons">
        <button className="btn" onClick={() => window.location.href = "#join"}>
          <span>JOIN</span>
        </button>
        <button className="btn" onClick={() => window.location.href = "#report"}>
          <span>REPORT</span>
        </button>
      </div>
    </div>
  );
};

export default Home;
