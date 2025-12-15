import React, { useMemo, useRef, useEffect, useState } from "react";
import "./Learn.css";

const Learn = () => {
  const viewportRef = useRef(null);
  const [slidesPerView, setSlidesPerView] = useState(1);

  // Build list of slide image URLs from public folder
  const slideImages = useMemo(() => {
    // Files live at: /pesukimApp/12 Pessukim Chanukah Slides/{01..24} 12 Pessukim Chanukah Slides.jpg
    return Array.from({ length: 24 }, (_, i) => {
      const idx = String(i + 1).padStart(2, "0");
      const path = `/pesukimApp/12 Pessukim Chanukah Slides/${idx} 12 Pessukim Chanukah Slides.jpg`;
      // Encode the full path to safely handle spaces
      return encodeURI(path);
    });
  }, []);

  const scrollByPage = (direction) => {
    const el = viewportRef.current;
    if (!el) return;
    const amount = el.clientWidth;
    el.scrollBy({ left: direction * amount, behavior: "smooth" });
  };

  useEffect(() => {
    const computeSlidesPerView = (width) => {
      if (width >= 1200) return 3;
      if (width >= 800) return 2;
      return 1;
    };

    const measure = () => {
      const w = viewportRef.current?.clientWidth || window.innerWidth || 0;
      setSlidesPerView(computeSlidesPerView(w));
    };

    // measure initially and on resize
    measure();
    window.addEventListener("resize", measure);
    return () => window.removeEventListener("resize", measure);
  }, []);

  useEffect(() => {
    const handleKey = (e) => {
      if (e.key === "ArrowRight") scrollByPage(1);
      if (e.key === "ArrowLeft") scrollByPage(-1);
    };
    window.addEventListener("keydown", handleKey);
    return () => window.removeEventListener("keydown", handleKey);
  }, []);

  return (
    <section className="learn-wrap">
      <div className="card card-blue learn-card">
        <h1 className="title-text">LEARN</h1>
        <p className="body-text text-center">12 Pessukim Academy</p>

        <div className="learn-carousel" aria-label="12 Pessukim slides">
          <div className="learn-carousel-viewport" ref={viewportRef}>
            <div
              className="learn-carousel-track"
              style={{ "--slides-per-view": slidesPerView }}
            >
              {slideImages.map((src, i) => (
                <div className="learn-slide" key={i}>
                  <img src={src} alt={`Slide ${i + 1}`} loading="lazy" />
                </div>
              ))}
            </div>
          </div>

          <div className="learn-carousel-nav">
            <button
              type="button"
              className="learn-carousel-arrow left"
              aria-label="Previous slide"
              onClick={() => scrollByPage(-1)}
            >
              ‹
            </button>
            <button
              type="button"
              className="learn-carousel-arrow right"
              aria-label="Next slide"
              onClick={() => scrollByPage(1)}
            >
              ›
            </button>
          </div>
        </div>
      </div>
    </section>
  );
};

export default Learn;