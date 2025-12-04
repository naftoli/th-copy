import React, { useEffect, useRef, useState } from "react";
import "./Prizes.css";

export const PrizeCard = ({ title, img, img2, alt, width, height, description }) => {
  const [isFlipped, setIsFlipped] = React.useState(false);
  const [isTouchDevice, setIsTouchDevice] = React.useState(false);

  React.useEffect(() => {
    // Detect if device supports touch
    setIsTouchDevice('ontouchstart' in window || navigator.maxTouchPoints > 0);
  }, []);

  const handleClick = () => {
    setIsFlipped(!isFlipped);
  };

  const handleMouseEnter = () => {
    if (!isTouchDevice) {
      setIsFlipped(true);
    }
  };

  const handleMouseLeave = () => {
    if (!isTouchDevice) {
      setIsFlipped(false);
    }
  };

  return (
    <div 
      className={`prize-card-flip ${isFlipped ? 'flipped' : ''}`}
      onClick={handleClick}
      onMouseEnter={handleMouseEnter}
      onMouseLeave={handleMouseLeave}
    >
      <div className="prize-card-inner">
        {/* Front of card */}
        <div className="prize-card-front">
          <h4 className="prize-title">{title}</h4>
          <div className="prize-figure-container">
            <div className="prize-figure">
              <img src={img ? 'https://mashpia.com/file_view.php?id=' + img : ''} alt={alt || title} width={width} height={height} />
              {img2 && (
                <img src={img2} alt={alt || title} width={width} height={height} />
              )}
            </div>
          </div>
        </div>
        
        {/* Back of card */}
        <div className="prize-card-back">
          {description && (
            <p className="prize-description">{description}</p>
          )}
        </div>
      </div>
    </div>
  );
}

function chunkCategories(categories) {
  const sections = [];
  let currentSection = [];
  let currentSectionPrizeCount = 0;

  for (const category of categories) {
    const categoryPrizeCount = category.prizes.length;
    const wouldExceedLimit = currentSectionPrizeCount + categoryPrizeCount > 8;

    // If adding this category would exceed 8 prizes, start a new section
    if (wouldExceedLimit && currentSection.length > 0) {
      sections.push(currentSection);
      currentSection = [category];
      currentSectionPrizeCount = categoryPrizeCount;
    } else {
      // Add to current section
      currentSection.push(category);
      currentSectionPrizeCount += categoryPrizeCount;
    }
  }

  // Add the last section if it has categories
  if (currentSection.length > 0) {
    sections.push(currentSection);
  }

  return sections;
}

export default function Prizes() {
  const [data, setData] = useState(null);
  const [err, setErr] = useState("");
  const scrollAreaRef = useRef(null);

  useEffect(() => {
    let on = true;
    (async () => {
      try {
        const res = await fetch("/api/pesukim/getPrizes.php", { cache: "no-store" });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (on) setData(json);
      } catch (e) {
        if (on) setErr("Couldn't load prizes.");
      }
    })();
    return () => { on = false; };
  }, []);

  useEffect(() => {
    // Wait for data to load so scroll area is rendered
    if (!data) return;

    const scrollArea = scrollAreaRef.current;
    if (!scrollArea) return;

    const handleWheel = (e) => {
      const { scrollTop, scrollHeight, clientHeight } = scrollArea;
      const threshold = 10; // Small threshold to detect near boundaries
      const isAtTop = scrollTop <= threshold;
      const isAtBottom = scrollTop + clientHeight >= scrollHeight - threshold;
      const scrollingUp = e.deltaY < 0;
      const scrollingDown = e.deltaY > 0;

      // If at top and scrolling up, scroll to missions section
      if (isAtTop && scrollingUp) {
        e.preventDefault();
        const missionsEl = document.getElementById("missions");
        if (missionsEl) {
          missionsEl.scrollIntoView({ behavior: "smooth" });
          window.location.hash = "missions";
        }
        return;
      }

      // If at bottom and scrolling down, scroll to watch-campaign section
      if (isAtBottom && scrollingDown) {
        e.preventDefault();
        const watchCampaignEl = document.getElementById("watch-campaign");
        if (watchCampaignEl) {
          watchCampaignEl.scrollIntoView({ behavior: "smooth" });
          window.location.hash = "watch-campaign";
        }
        return;
      }
    };

    scrollArea.addEventListener('wheel', handleWheel, { passive: false });

    return () => {
      scrollArea.removeEventListener('wheel', handleWheel);
    };
  }, [data]);

  if (err) return <div className="prizes-error card card-blue">{err}</div>;
  if (!data) return <div className="prizes-loading card card-blue">Loading…</div>;

  // Group categories into sections (2 per section, unless total prizes would exceed 8)
  const categorySections = chunkCategories(data.categories || []);

  return (
    <section className="prizes">
      {/* Fixed header */}
      <div className="prizes-header">
        <h1 className="title-text">AUCTION PRIZES</h1>
        <p className="body-text prizes-subtitle text-center">
          When you join and do missions you will earn miles that you can cash in to buy tickets
          in this year's Tzivos Hashem Auction for a chance to win amazing prizes including:
        </p>
      </div>

      {/* Scrollable snap sections */}
      <div className="prizes-scroll-area" ref={scrollAreaRef}>
        {categorySections.map((sectionCategories, sectionIndex) => (
          <div key={sectionIndex} className="prize-section">
            {sectionCategories.map((category, catIndex) => (
              <>
                <h3 className="prize-category">{category.name}</h3>
                <div className={`prize-grid ${category.gridClass}`}>
                  {category.prizes.map((prize, prizeIndex) => (
                    <PrizeCard
                      key={prizeIndex}
                      title={prize.title}
                      img={prize.img}
                      img2={prize.img2}
                      alt={prize.alt}
                      width={prize.width}
                      height={prize.height}
                      description={prize.description}
                    />
                  ))}
                </div>
              </>
            ))}
          </div>
        ))}
      </div>
    </section>
  );
}
